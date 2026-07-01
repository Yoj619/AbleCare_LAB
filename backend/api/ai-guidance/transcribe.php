<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        [$key, $value] = array_map('trim', $parts);
        $value = trim($value, '"\'');
        if ($key !== '') {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function jsonError(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

// ─── Load environment ─────────────────────────────────────────────────────────
$envPath = dirname(__DIR__, 3) . '/.env';
loadEnv($envPath);

$apiKey = getenv('GEMINI_API_KEY') ?: '';
if ($apiKey === '') {
    jsonError(500, 'AI service not configured. Set GEMINI_API_KEY in .env');
}

// ─── Parse request ────────────────────────────────────────────────────────────
$rawBody = (string) file_get_contents('php://input');
$body    = json_decode($rawBody, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    jsonError(400, 'Invalid JSON body');
}

$audioBase64 = trim((string) ($body['audio'] ?? ''));
$mimeType    = trim((string) ($body['mimeType'] ?? 'audio/m4a'));

if ($audioBase64 === '') {
    jsonError(400, 'Missing audio data');
}

// Only allow safe audio MIME types
$allowedMimeTypes = ['audio/m4a', 'audio/mp4', 'audio/aac', 'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/webm', 'audio/ogg'];
if (!in_array($mimeType, $allowedMimeTypes, true)) {
    jsonError(400, 'Unsupported audio format');
}

// ─── Call Gemini multimodal API ───────────────────────────────────────────────
$geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

$requestBody = [
    'contents' => [[
        'parts' => [
            [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data'      => $audioBase64,
                ],
            ],
            [
                'text' => 'Transcribe this voice recording exactly as spoken. The speaker is a caregiver describing their patient\'s symptoms or asking a health question. Return only the transcribed text with no extra commentary, labels, or formatting.',
            ],
        ],
    ]],
    'generationConfig' => [
        'responseMimeType' => 'text/plain',
        'temperature'      => 0.0,
    ],
];

$ch = curl_init($geminiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($requestBody),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
]);

$raw      = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($raw === false || $curlErr !== '') {
    jsonError(503, 'Could not reach the AI transcription service.');
}

if ($httpCode === 429) {
    jsonError(429, 'Transcription quota exceeded. Please wait a moment and try again.');
}

if ($httpCode !== 200) {
    jsonError(502, 'Transcription service returned an unexpected response.');
}

$geminiData = json_decode((string) $raw, true);

$transcript = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';
$transcript = trim(strip_tags((string) $transcript));

if ($transcript === '') {
    jsonError(500, 'Could not transcribe the audio. Please try speaking more clearly or type your question.');
}

echo json_encode(['transcript' => $transcript]);
