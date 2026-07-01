<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db   = get_db();
$user = require_auth($db);

$stmt = $db->prepare(
    'SELECT id, title, message, type, is_read, created_at FROM notifications
     WHERE user_id = ? ORDER BY created_at DESC'
);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id'        => (int) $row['id'],
        'title'     => $row['title'],
        'message'   => $row['message'],
        'type'      => $row['type'],
        'isRead'    => (bool) $row['is_read'],
        'createdAt' => $row['created_at'],
    ];
}
$stmt->close();

json_success($notifications);
