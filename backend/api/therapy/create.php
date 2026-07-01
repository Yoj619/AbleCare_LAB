<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db   = get_db();
$user = require_auth($db);

$body = get_json_body();
require_fields($body, ['patient_id', 'healthcare_provider_id', 'session_date', 'session_time']);

$patientId   = (int) $body['patient_id'];
$providerId  = (int) $body['healthcare_provider_id'];
$sessionDate = (string) $body['session_date'];
$sessionTime = (string) $body['session_time'];
$notes       = !empty($body['notes']) ? (string) $body['notes'] : null;

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
    'INSERT INTO therapy_schedules (patient_id, healthcare_provider_id, session_date, session_time, notes)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->bind_param('iisss', $patientId, $providerId, $sessionDate, $sessionTime, $notes);
$stmt->execute();
$sessionId = $stmt->insert_id;
$stmt->close();

json_success(['id' => $sessionId, 'status' => 'scheduled'], 201);
