<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$body = get_json_body();

$ids = [];
if (isset($body['ids']) && is_array($body['ids'])) {
    $ids = array_map('intval', $body['ids']);
} elseif (isset($body['id'])) {
    $ids = [(int) $body['id']];
} else {
    json_error(422, 'Provide either "id" or "ids".');
}

$ids = array_values(array_filter($ids, fn ($id) => $id > 0));
if (empty($ids)) {
    json_error(422, 'No valid notification ids were provided.');
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types        = str_repeat('i', count($ids)) . 'i';
$params       = array_merge($ids, [$user['id']]);

$stmt = $db->prepare(
    "UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders) AND user_id = ?"
);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();

json_success(['message' => 'Notifications marked as read.']);
