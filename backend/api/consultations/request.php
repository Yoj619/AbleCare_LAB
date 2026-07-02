<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db        = get_db();
$caregiver = require_caregiver($db);
$cgId      = $caregiver['caregiver_id'];

$body = get_json_body();
require_fields($body, ['provider_id', 'patient_id']);

$providerId = (int) $body['provider_id'];
$patientId  = (int) $body['patient_id'];

// Verify the patient belongs to this caregiver
$stmt = $db->prepare(
    'SELECT id FROM patients WHERE id = ? AND caregiver_id = ? AND deleted_at IS NULL LIMIT 1'
);
$stmt->bind_param('ii', $patientId, $cgId);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    json_error(404, 'Patient not found or does not belong to your account.');
}

// Verify the provider exists and is approved
$stmt = $db->prepare(
    'SELECT hp.id FROM healthcare_providers hp
     JOIN users u ON u.id = hp.user_id
     WHERE hp.id = ? AND u.status = ? LIMIT 1'
);
$approved = 'approved';
$stmt->bind_param('is', $providerId, $approved);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$provider) {
    json_error(404, 'Healthcare provider not found or not yet approved.');
}

// Idempotency: return existing pending/accepted consultation if one exists
$stmt = $db->prepare(
    "SELECT id, status, notes, created_at, updated_at FROM consultations
     WHERE caregiver_id = ? AND healthcare_provider_id = ? AND patient_id = ?
       AND status IN ('pending', 'accepted')
     ORDER BY created_at DESC LIMIT 1"
);
$stmt->bind_param('iii', $cgId, $providerId, $patientId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    json_success([
        'id'        => (int) $existing['id'],
        'status'    => $existing['status'],
        'notes'     => $existing['notes'],
        'createdAt' => $existing['created_at'],
        'updatedAt' => $existing['updated_at'],
        'alreadyExists' => true,
    ]);
}

// Insert new consultation
$stmt = $db->prepare(
    "INSERT INTO consultations (patient_id, caregiver_id, healthcare_provider_id, status)
     VALUES (?, ?, ?, 'pending')"
);
$stmt->bind_param('iii', $patientId, $cgId, $providerId);
$stmt->execute();
$consultationId = (int) $stmt->insert_id;
$stmt->close();

$now = date('Y-m-d H:i:s');
json_success([
    'id'            => $consultationId,
    'status'        => 'pending',
    'notes'         => null,
    'createdAt'     => $now,
    'updatedAt'     => $now,
    'alreadyExists' => false,
], 201);
