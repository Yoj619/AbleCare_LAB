<?php
declare(strict_types=1);

// ─── CORS & response headers ──────────────────────────────────────────────────
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
        // Strip surrounding quotes from value
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

function appendLog(string $logDir, string $message): void
{
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $entry = date('Y-m-d H:i:s') . ' | ' . $message . PHP_EOL;
    file_put_contents($logDir . '/ai-guidance.log', $entry, FILE_APPEND | LOCK_EX);
}

// ─── Load environment ─────────────────────────────────────────────────────────
// .env lives at AbleCare/ root, three directories above this file
$envPath = dirname(__DIR__, 3) . '/.env';
loadEnv($envPath);

$apiKey = getenv('GEMINI_API_KEY') ?: '';
if ($apiKey === '') {
    jsonError(500, 'AI service not configured. Set GEMINI_API_KEY in .env');
}

// ─── Parse & validate request body ───────────────────────────────────────────
$rawBody = (string) file_get_contents('php://input');
$body    = json_decode($rawBody, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    jsonError(400, 'Invalid JSON body');
}

$symptoms       = trim((string) ($body['symptoms']       ?? ''));
$disabilityType = trim((string) ($body['disabilityType'] ?? ''));
$medicalHistory = trim((string) ($body['medicalHistory'] ?? ''));

if ($symptoms === '') {
    jsonError(400, 'Symptoms field is required');
}

// ─── Log request (no sensitive patient data) ──────────────────────────────────
$logDir = dirname(__DIR__, 3) . '/logs';
appendLog($logDir, sprintf(
    'REQUEST | DisabilityType: %s | WordCount: %d | IP: %s',
    $disabilityType ?: 'N/A',
    str_word_count($symptoms),
    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
));

// ─── Build system prompt ──────────────────────────────────────────────────────
$systemPrompt = <<<PROMPT
You are AbleCare's AI health guidance assistant supporting caregivers of elderly and PWD (persons with disability) patients in the Philippines.

Your role:
- Provide concise, actionable preliminary care steps based on reported symptoms.
- Assess urgency using one of exactly three severity levels.
- Always remind caregivers that your guidance is NOT a substitute for professional medical care.
- Keep language simple and clear — these are non-medical caregivers in a community setting.

Severity definitions:
- "high"   → Potentially life-threatening: chest pain, severe difficulty breathing, loss of consciousness, uncontrolled bleeding, stroke signs (face drooping, arm weakness, slurred speech), seizures, anaphylaxis.
- "medium" → Concerning but not immediately life-threatening: fever above 38.5 °C, moderate or worsening pain, falls without apparent serious injury, confusion/disorientation, persistent vomiting or diarrhoea.
- "low"    → Minor, manageable at home: mild headache, small cuts/abrasions, mild cold or cough symptoms, mild fatigue, minor bruising.

Response format — return ONLY valid JSON, no markdown, no code fences, no extra text:
{
  "guidance": "Numbered list of 3–5 actionable steps for the caregiver, each on a new line.",
  "disclaimer": "One concise sentence reminding the caregiver to seek professional medical advice when needed.",
  "severity": "low" | "medium" | "high"
}
PROMPT;

// ─── Build user message ───────────────────────────────────────────────────────
$userLines = ["Patient report:"];
if ($disabilityType !== '') {
    $userLines[] = "- Disability / condition: $disabilityType";
}
if ($medicalHistory !== '') {
    $userLines[] = "- Medical history: $medicalHistory";
}
$userLines[] = "- Current symptoms: $symptoms";
$userLines[] = "";
$userLines[] = "Please provide guidance for the caregiver.";
$userMessage = implode("\n", $userLines);

// ─── Gemini API request ───────────────────────────────────────────────────────
$geminiUrl = sprintf(
    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=%s',
    urlencode($apiKey)
);

$requestPayload = [
    'system_instruction' => [
        'parts' => [['text' => $systemPrompt]],
    ],
    'contents' => [
        [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]],
        ],
    ],
    'generationConfig' => [
        'temperature'      => 0.3,
        'maxOutputTokens'  => 600,
        'responseMimeType' => 'application/json',
        'responseSchema'   => [
            'type'       => 'OBJECT',
            'properties' => [
                'guidance'    => ['type' => 'STRING'],
                'disclaimer'  => ['type' => 'STRING'],
                'severity'    => [
                    'type' => 'STRING',
                    'enum' => ['low', 'medium', 'high'],
                ],
            ],
            'required' => ['guidance', 'disclaimer', 'severity'],
        ],
    ],
];

$ch = curl_init($geminiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($requestPayload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

$rawResponse = curl_exec($ch);
$httpCode    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError   = curl_error($ch);
curl_close($ch);

// ─── Handle transport / HTTP errors ──────────────────────────────────────────
if ($curlError !== '') {
    appendLog($logDir, "CURL_ERROR | $curlError");
    jsonError(503, 'Unable to reach the AI service. Please check your connection and try again.');
}

if ($httpCode === 429) {
    appendLog($logDir, 'RATE_LIMITED | Gemini returned 429');
    jsonError(429, 'AI service is busy right now. Please wait a moment and try again.');
}

if ($httpCode !== 200) {
    appendLog($logDir, "GEMINI_HTTP_ERROR | Status: $httpCode | Body: " . substr((string) $rawResponse, 0, 200));
    jsonError(502, 'AI service returned an unexpected error. Please try again.');
}

// ─── Parse Gemini response ────────────────────────────────────────────────────
$geminiData = json_decode((string) $rawResponse, true);
$aiRawText  = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? null;

if ($aiRawText === null || $aiRawText === '') {
    // Check for safety-blocked response
    $blockReason = $geminiData['candidates'][0]['finishReason'] ?? 'UNKNOWN';
    appendLog($logDir, "EMPTY_RESPONSE | FinishReason: $blockReason");
    jsonError(502, 'No guidance could be generated for these symptoms. Please consult a medical professional directly.');
}

// Parse the structured JSON the model returned
$aiResult = json_decode(trim($aiRawText), true);

// Fallback: extract JSON block if model wrapped it in markdown fences
if (json_last_error() !== JSON_ERROR_NONE) {
    preg_match('/\{.*\}/s', $aiRawText, $matches);
    if (!empty($matches[0])) {
        $aiResult = json_decode($matches[0], true);
    }
}

if (
    !is_array($aiResult) ||
    !isset($aiResult['guidance'], $aiResult['disclaimer'], $aiResult['severity'])
) {
    appendLog($logDir, 'PARSE_FAIL | Raw: ' . substr($aiRawText, 0, 200));
    jsonError(502, 'Could not parse AI response. Please try again.');
}

// ─── Validate & sanitise output ───────────────────────────────────────────────
$validSeverities = ['low', 'medium', 'high'];
$severity = in_array($aiResult['severity'], $validSeverities, true)
    ? $aiResult['severity']
    : 'medium';

$guidance   = strip_tags((string) $aiResult['guidance']);
$disclaimer = strip_tags((string) $aiResult['disclaimer']);

// ─── Log success ──────────────────────────────────────────────────────────────
appendLog($logDir, "SUCCESS | Severity: $severity");

// ─── Return clean response ────────────────────────────────────────────────────
echo json_encode([
    'guidance'    => $guidance,
    'disclaimer'  => $disclaimer,
    'severity'    => $severity,
], JSON_UNESCAPED_UNICODE);
