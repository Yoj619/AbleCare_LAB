<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db   = get_db();
$user = require_auth($db);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    json_error(422, 'A valid id is required.');
}

$stmt = $db->prepare(
    'SELECT hr.id, hr.patient_id, hr.record_type, hr.notes, hr.recorded_by, hr.recorded_at, p.caregiver_id
     FROM health_records hr
     JOIN patients p ON p.id = hr.patient_id
     WHERE hr.id = ? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    json_error(404, 'Health record not found.');
}

if ($user['role'] === 'caregiver') {
    $stmt = $db->prepare('SELECT id FROM caregivers WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $row['caregiver_id'], $user['id']);
    $stmt->execute();
    $owns = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$owns) {
        json_error(404, 'Health record not found.');
    }
}

json_success([
    'id'         => (int) $row['id'],
    'patientId'  => (int) $row['patient_id'],
    'recordType' => $row['record_type'],
    'notes'      => $row['notes'],
    'recordedBy' => (int) $row['recorded_by'],
    'recordedAt' => $row['recorded_at'],
]);
