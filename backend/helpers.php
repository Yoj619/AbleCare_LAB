<?php
declare(strict_types=1);

// ============================================================
//  AbleCare – Shared API helpers
//  Every endpoint under backend/api/** requires this file.
//  Response envelope is always: { "data": ..., "error": ... }
// ============================================================

require_once __DIR__ . '/db.php';

// This XAMPP install's php.ini sets serialize_precision=100, which makes
// json_encode() dump full binary float expansions (e.g. 1.0399999999...)
// instead of the shortest round-trip representation. -1 is PHP's own
// recommended default since 7.1; override it here rather than editing the
// shared system php.ini.
ini_set('serialize_precision', '-1');

function send_cors_headers(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function require_method(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        json_error(405, 'Method not allowed');
    }
}

function json_success(mixed $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode(['data' => $data, 'error' => null], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(int $status, string $message): never {
    http_response_code($status);
    echo json_encode(['data' => null, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Decodes the JSON request body into an associative array. */
function get_json_body(): array {
    $raw = (string) file_get_contents('php://input');
    if ($raw === '') return [];

    $body = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
        json_error(400, 'Invalid JSON body');
    }
    return $body;
}

/** Ensures every key in $required is present and non-empty in $body. */
function require_fields(array $body, array $required): void {
    $missing = [];
    foreach ($required as $field) {
        if (!isset($body[$field]) || (is_string($body[$field]) && trim($body[$field]) === '')) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        json_error(422, 'Missing required field(s): ' . implode(', ', $missing));
    }
}

/**
 * Validates the "Authorization: Bearer <token>" header against users.api_token.
 * Returns the authenticated user row. Exits with 401 if missing/invalid.
 */
function get_authorization_header(): string {
    // Apache/mod_php commonly strips the Authorization header from $_SERVER —
    // fall back to getallheaders() (case-insensitive lookup) when that happens.
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return (string) $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                return (string) $value;
            }
        }
    }
    return '';
}

function require_auth(mysqli $db): array {
    $header = get_authorization_header();
    if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        json_error(401, 'Missing or malformed Authorization header');
    }
    $token = trim($matches[1]);

    $stmt = $db->prepare(
        'SELECT id, role, first_name, last_name, email, status FROM users WHERE api_token = ? LIMIT 1'
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        json_error(401, 'Invalid or expired session. Please log in again.');
    }
    if ($user['status'] !== 'approved') {
        json_error(403, 'Your account is not yet approved by an administrator.');
    }

    return $user;
}

/** Like require_auth(), but also resolves the caller's caregivers.id row. */
function require_caregiver(mysqli $db): array {
    $user = require_auth($db);
    if ($user['role'] !== 'caregiver') {
        json_error(403, 'This action is only available to caregivers.');
    }

    $stmt = $db->prepare('SELECT id FROM caregivers WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $caregiver = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$caregiver) {
        json_error(404, 'No caregiver profile found for this account.');
    }

    $user['caregiver_id'] = (int) $caregiver['id'];
    return $user;
}

function generate_api_token(): string {
    return bin2hex(random_bytes(32));
}

/**
 * Rounds a float and re-stringifies it so json_encode emits a clean value.
 * Needed because some PHP builds set serialize_precision very high, which
 * makes json_encode dump long binary-float artifacts (e.g. 1.04000000000007).
 */
function clean_float(?float $value, int $decimals = 2): ?float {
    if ($value === null) return null;
    return (float) sprintf("%.{$decimals}f", $value);
}
