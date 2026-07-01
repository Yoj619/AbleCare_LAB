<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$body = get_json_body();
require_fields($body, ['receiver_id', 'message_text']);

$receiverId  = (int) $body['receiver_id'];
$messageText = trim((string) $body['message_text']);

if ($receiverId === (int) $user['id']) {
    json_error(422, 'Cannot send a message to yourself.');
}

$stmt = $db->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $receiverId);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    json_error(404, 'Recipient not found.');
}

$stmt = $db->prepare('INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)');
$stmt->bind_param('iis', $user['id'], $receiverId, $messageText);
$stmt->execute();
$messageId = $stmt->insert_id;
$stmt->close();

json_success(['id' => $messageId], 201);
