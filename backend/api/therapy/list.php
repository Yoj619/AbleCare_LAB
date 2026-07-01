<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db   = get_db();
$user = require_auth($db);

$patientId = (int) ($_GET['patient_id'] ?? 0);
if ($patientId <= 0) {
    json_error(422, 'A valid patient_id is required.');
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
    "SELECT ts.id, ts.healthcare_provider_id, ts.session_date, ts.session_time, ts.status, ts.notes,
            u.first_name AS provider_first_name, u.last_name AS provider_last_name
     FROM therapy_schedules ts
     JOIN healthcare_providers hp ON hp.id = ts.healthcare_provider_id
     JOIN users u ON u.id = hp.user_id
     WHERE ts.patient_id = ?
     ORDER BY ts.session_date ASC, ts.session_time ASC"
);
$stmt->bind_param('i', $patientId);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sessions[] = [
        'id'                 => (int) $row['id'],
        'healthProviderId'   => (int) $row['healthcare_provider_id'],
        'providerName'       => trim($row['provider_first_name'] . ' ' . $row['provider_last_name']),
        'sessionDate'        => $row['session_date'],
        'sessionTime'        => $row['session_time'],
        'status'             => $row['status'],
        'notes'              => $row['notes'],
    ];
}
$stmt->close();

json_success($sessions);
