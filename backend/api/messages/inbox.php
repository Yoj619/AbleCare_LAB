<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db   = get_db();
$user = require_auth($db);

// For every message involving me, find the "other party" and keep only the
// most recent message per other-party, plus an unread count from them.
$stmt = $db->prepare(
    "SELECT
        other_id,
        m.message_text, m.sent_at, m.sender_id,
        u.first_name, u.last_name, u.role,
        (SELECT COUNT(*) FROM messages WHERE sender_id = other_id AND receiver_id = ? AND is_read = 0) AS unread_count
     FROM (
        SELECT
            CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_id,
            MAX(id) AS last_message_id
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY other_id
     ) latest
     JOIN messages m ON m.id = latest.last_message_id
     JOIN users u ON u.id = latest.other_id
     ORDER BY m.sent_at DESC"
);
$stmt->bind_param('iiii', $user['id'], $user['id'], $user['id'], $user['id']);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = [
        'userId'        => (int) $row['other_id'],
        'name'          => trim($row['first_name'] . ' ' . $row['last_name']),
        'role'          => $row['role'],
        'lastMessage'   => $row['message_text'],
        'lastMessageAt' => $row['sent_at'],
        'fromMe'        => (int) $row['sender_id'] === (int) $user['id'],
        'unreadCount'   => (int) $row['unread_count'],
    ];
}
$stmt->close();

json_success($conversations);
