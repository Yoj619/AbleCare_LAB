<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$body = get_json_body();
require_fields($body, ['name', 'email', 'password', 'phone']);

$name      = trim((string) $body['name']);
$email     = trim((string) $body['email']);
$password  = (string) $body['password'];
$phone     = trim((string) $body['phone']);
$address   = trim((string) ($body['address']  ?? ''));
$barangay  = trim((string) ($body['barangay'] ?? ''));
$lat       = isset($body['latitude'])  && is_numeric($body['latitude'])  ? (float) $body['latitude']  : null;
$lng       = isset($body['longitude']) && is_numeric($body['longitude']) ? (float) $body['longitude'] : null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error(422, 'A valid email address is required.');
}
if (strlen($password) < 8) {
    json_error(422, 'Password must be at least 8 characters.');
}
if ($name === '') {
    json_error(422, 'Full name is required.');
}
if ($phone === '') {
    json_error(422, 'Phone number is required.');
}

$nameParts = preg_split('/\s+/', $name, 2);
$firstName = $nameParts[0] ?? '';
$lastName  = $nameParts[1] ?? '';

$db = get_db();

$stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    json_error(409, 'An account with that email already exists.');
}
$stmt->close();

$hash   = password_hash($password, PASSWORD_BCRYPT);
$role   = 'caregiver';
$status = 'approved'; // caregivers are auto-approved; only providers require admin review

$token  = generate_api_token();
$userId = 0;

$db->begin_transaction();
try {
    $stmt = $db->prepare(
        'INSERT INTO users (role, first_name, last_name, email, password, phone_number, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssssss', $role, $firstName, $lastName, $email, $hash, $phone, $status);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    if ($lat !== null && $lng !== null) {
        $stmt = $db->prepare('INSERT INTO caregivers (user_id, address, barangay, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issdd', $userId, $address, $barangay, $lat, $lng);
    } else {
        $stmt = $db->prepare('INSERT INTO caregivers (user_id, address, barangay) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $userId, $address, $barangay);
    }
    $stmt->execute();
    $stmt->close();

    // Store token so the app can immediately call authenticated endpoints
    // (e.g. createPatient) without requiring a separate login step.
    $stmt = $db->prepare('UPDATE users SET api_token = ? WHERE id = ?');
    $stmt->bind_param('si', $token, $userId);
    $stmt->execute();
    $stmt->close();

    $db->commit();
} catch (\Throwable $e) {
    $db->rollback();
    json_error(500, 'Registration failed. Please try again.');
}

json_success([
    'token' => $token,
    'user'  => [
        'id'        => $userId,
        'role'      => $role,
        'firstName' => $firstName,
        'lastName'  => $lastName,
        'email'     => $email,
    ],
], 201);
