<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$body = get_json_body();
require_fields($body, ['patient_id', 'record_type']);

$patientId  = (int) $body['patient_id'];
$recordType = (string) $body['record_type'];
$notes      = !empty($body['notes']) ? (string) $body['notes'] : null;

$validTypes = ['vitals', 'symptom_log', 'medication', 'general'];
if (!in_array($recordType, $validTypes, true)) {
    json_error(422, 'Invalid record_type.');
}

if ($user['role'] === 'caregiver') {
    $stmt = $db->prepare(
        'SELECT p.id FROM patients p
         JOIN caregivers c ON c.id = p.caregiver_id
         WHERE p.id = ? AND c.user_id = ? AND p.deleted_at IS NULL LIMIT 1'
    );
    $stmt->bind_param('ii', $patientId, $user['id']);
    $stmt->execute();
    $owns = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$owns) {
        json_error(404, 'Patient not found.');
    }
}

$stmt = $db->prepare(
    'INSERT INTO health_records (patient_id, record_type, notes, recorded_by) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('issi', $patientId, $recordType, $notes, $user['id']);
$stmt->execute();
$recordId = $stmt->insert_id;
$stmt->close();

json_success(['id' => $recordId], 201);
