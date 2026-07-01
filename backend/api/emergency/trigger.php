<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db        = get_db();
$caregiver = require_caregiver($db);

$body = get_json_body();
require_fields($body, ['patient_id', 'latitude', 'longitude']);

$patientId = (int) $body['patient_id'];

// ── Validate coordinates ──────────────────────────────────────────────────
if (!is_numeric($body['latitude']) || !is_numeric($body['longitude'])) {
    json_error(422, 'Valid latitude and longitude are required.');
}

$latitude  = (float) $body['latitude'];
$longitude = (float) $body['longitude'];

if ($latitude < -90.0 || $latitude > 90.0) {
    json_error(422, 'Latitude must be between -90 and 90.');
}
if ($longitude < -180.0 || $longitude > 180.0) {
    json_error(422, 'Longitude must be between -180 and 180.');
}

// ── Verify patient belongs to this caregiver ──────────────────────────────
$stmt = $db->prepare(
    'SELECT id FROM patients WHERE id = ? AND caregiver_id = ? AND deleted_at IS NULL LIMIT 1'
);
$stmt->bind_param('ii', $patientId, $caregiver['caregiver_id']);
$stmt->execute();
$owns = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$owns) {
    json_error(404, 'Patient not found.');
}

// ── Insert alert with live GPS coordinates ────────────────────────────────
$stmt = $db->prepare(
    'INSERT INTO emergency_alerts (patient_id, caregiver_id, latitude, longitude, status)
     VALUES (?, ?, ?, ?, "active")'
);
$stmt->bind_param('iidd', $patientId, $caregiver['caregiver_id'], $latitude, $longitude);
$stmt->execute();
$alertId = $stmt->insert_id;
$stmt->close();

// ── Read back the server-assigned created_at ──────────────────────────────
$stmt = $db->prepare('SELECT created_at FROM emergency_alerts WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $alertId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$triggeredAt = ($row !== null) ? $row['created_at'] : date('Y-m-d H:i:s');

json_success([
    'id'          => $alertId,
    'status'      => 'active',
    'triggeredAt' => $triggeredAt,
], 201);
