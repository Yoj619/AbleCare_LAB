<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db   = get_db();
$user = require_auth($db);

$otherUserId = (int) ($_GET['with'] ?? 0);
if ($otherUserId <= 0) {
    json_error(422, 'A valid "with" user id is required.');
}

$stmt = $db->prepare(
    'SELECT id, sender_id, receiver_id, message_text, is_read, sent_at
     FROM messages
     WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
     ORDER BY sent_at ASC'
);
$stmt->bind_param('iiii', $user['id'], $otherUserId, $otherUserId, $user['id']);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id'          => (int) $row['id'],
        'senderId'    => (int) $row['sender_id'],
        'receiverId'  => (int) $row['receiver_id'],
        'messageText' => $row['message_text'],
        'isRead'      => (bool) $row['is_read'],
        'sentAt'      => $row['sent_at'],
    ];
}
$stmt->close();

// Mark incoming messages from the other user as read
$stmt = $db->prepare('UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0');
$stmt->bind_param('ii', $otherUserId, $user['id']);
$stmt->execute();
$stmt->close();

json_success($messages);
