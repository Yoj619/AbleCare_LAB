<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$body = get_json_body();
require_fields($body, ['id']);
$id = (int) $body['id'];

$stmt = $db->prepare('SELECT ea.id, ea.caregiver_id FROM emergency_alerts ea WHERE ea.id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$alert = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$alert) {
    json_error(404, 'Emergency alert not found.');
}

if ($user['role'] !== 'admin') {
    $stmt = $db->prepare('SELECT id FROM caregivers WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $alert['caregiver_id'], $user['id']);
    $stmt->execute();
    $owns = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$owns) {
        json_error(403, 'You are not authorized to resolve this alert.');
    }
}

$stmt = $db->prepare(
    'UPDATE emergency_alerts SET status = "resolved", resolved_at = NOW() WHERE id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

json_success(['message' => 'Emergency alert resolved.']);
