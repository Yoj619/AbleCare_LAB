<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$body = get_json_body();
require_fields($body, ['email', 'password']);

$email    = trim((string) $body['email']);
$password = (string) $body['password'];

$db = get_db();
$stmt = $db->prepare('SELECT id, role, first_name, last_name, email, password, status FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    json_error(401, 'Invalid email or password.');
}

if ($user['status'] === 'pending') {
    json_error(403, 'Your account is still pending admin approval.');
}
if ($user['status'] === 'rejected') {
    json_error(403, 'Your account registration was rejected. Please contact the LGU office.');
}

$token = generate_api_token();
$stmt = $db->prepare('UPDATE users SET api_token = ? WHERE id = ?');
$stmt->bind_param('si', $token, $user['id']);
$stmt->execute();
$stmt->close();

json_success([
    'token' => $token,
    'user'  => [
        'id'        => (int) $user['id'],
        'role'      => $user['role'],
        'firstName' => $user['first_name'],
        'lastName'  => $user['last_name'],
        'email'     => $user['email'],
    ],
]);
