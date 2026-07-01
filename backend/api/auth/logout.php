<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$stmt = $db->prepare('UPDATE users SET api_token = NULL WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->close();

json_success(['message' => 'Logged out successfully.']);
