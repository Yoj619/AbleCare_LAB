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
    'SELECT id, record_type, notes, recorded_by, recorded_at FROM health_records
     WHERE patient_id = ? ORDER BY recorded_at DESC'
);
$stmt->bind_param('i', $patientId);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = [
        'id'         => (int) $row['id'],
        'recordType' => $row['record_type'],
        'notes'      => $row['notes'],
        'recordedBy' => (int) $row['recorded_by'],
        'recordedAt' => $row['recorded_at'],
    ];
}
$stmt->close();

json_success($records);
