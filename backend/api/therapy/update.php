<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$body = get_json_body();
require_fields($body, ['id', 'status']);

$id     = (int) $body['id'];
$status = (string) $body['status'];
$notes  = array_key_exists('notes', $body) ? (string) $body['notes'] : null;

$validStatuses = ['scheduled', 'completed', 'missed', 'cancelled'];
if (!in_array($status, $validStatuses, true)) {
    json_error(422, 'Invalid status.');
}

$stmt = $db->prepare('SELECT id FROM therapy_schedules WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    json_error(404, 'Therapy session not found.');
}

if ($notes !== null) {
    $stmt = $db->prepare('UPDATE therapy_schedules SET status = ?, notes = ? WHERE id = ?');
    $stmt->bind_param('ssi', $status, $notes, $id);
} else {
    $stmt = $db->prepare('UPDATE therapy_schedules SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
}
$stmt->execute();
$stmt->close();

json_success(['message' => 'Therapy session updated.']);
