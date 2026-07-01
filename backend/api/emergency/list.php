<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db   = get_db();
$user = require_auth($db);

if ($user['role'] !== 'admin') {
    json_error(403, 'This action is only available to administrators.');
}

// Show all in-progress alerts (active + responded) — resolved alerts drop off the live feed
$stmt = $db->prepare(
    "SELECT ea.id, ea.patient_id, ea.caregiver_id, ea.status, ea.created_at,
            ea.latitude, ea.longitude,
            p.first_name  AS patient_first_name,  p.last_name  AS patient_last_name,
            u.first_name  AS caregiver_first_name, u.last_name AS caregiver_last_name,
            u.phone_number
     FROM emergency_alerts ea
     JOIN patients   p ON p.id = ea.patient_id
     JOIN caregivers c ON c.id = ea.caregiver_id
     JOIN users      u ON u.id = c.user_id
     WHERE ea.status IN ('active', 'responded')
     ORDER BY ea.created_at DESC"
);
$stmt->execute();
$result = $stmt->get_result();

$alerts = [];
while ($row = $result->fetch_assoc()) {
    $lat = $row['latitude']  !== null ? (float) $row['latitude']  : null;
    $lng = $row['longitude'] !== null ? (float) $row['longitude'] : null;

    $alerts[] = [
        'id'             => (int) $row['id'],
        'patientId'      => (int) $row['patient_id'],
        'patientName'    => trim($row['patient_first_name'] . ' ' . $row['patient_last_name']),
        'caregiverId'    => (int) $row['caregiver_id'],
        'caregiverName'  => trim($row['caregiver_first_name'] . ' ' . $row['caregiver_last_name']),
        'caregiverPhone' => $row['phone_number'],
        'latitude'       => $lat !== null ? clean_float($lat, 7) : null,
        'longitude'      => $lng !== null ? clean_float($lng, 7) : null,
        'locationType'   => 'Live GPS',
        'status'         => $row['status'],
        'triggeredAt'    => $row['created_at'],
    ];
}
$stmt->close();

json_success($alerts);
