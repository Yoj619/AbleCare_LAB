<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$body = get_json_body();
require_fields($body, ['name', 'email', 'password', 'phone', 'address', 'barangay', 'latitude', 'longitude']);

$name      = trim((string) $body['name']);
$email     = trim((string) $body['email']);
$password  = (string) $body['password'];
$phone     = trim((string) $body['phone']);
$address   = trim((string) $body['address']);
$barangay  = trim((string) $body['barangay']);
$latitude  = $body['latitude'];
$longitude = $body['longitude'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error(422, 'A valid email address is required.');
}
if (strlen($password) < 8) {
    json_error(422, 'Password must be at least 8 characters.');
}
if (!is_numeric($latitude) || !is_numeric($longitude)) {
    json_error(422, 'A valid map location is required.');
}
$lat = (float) $latitude;
$lng = (float) $longitude;
if ($lat < -90 || $lat > 90) {
    json_error(422, 'Latitude must be between -90 and 90.');
}
if ($lng < -180 || $lng > 180) {
    json_error(422, 'Longitude must be between -180 and 180.');
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

$hash = password_hash($password, PASSWORD_BCRYPT);
$role = 'caregiver';

$db->begin_transaction();
try {
    $stmt = $db->prepare(
        'INSERT INTO users (role, first_name, last_name, email, password, phone_number) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssss', $role, $firstName, $lastName, $email, $hash, $phone);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare('INSERT INTO caregivers (user_id, address, barangay, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issdd', $userId, $address, $barangay, $lat, $lng);
    $stmt->execute();
    $stmt->close();

    $db->commit();
} catch (\Throwable $e) {
    $db->rollback();
    json_error(500, 'Registration failed. Please try again.');
}

json_success([
    'message' => 'Your account has been created. Please wait for admin approval before you can log in.',
], 201);
