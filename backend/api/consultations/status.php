<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db        = get_db();
$caregiver = require_caregiver($db);
$cgId      = $caregiver['caregiver_id'];

$providerId = isset($_GET['provider_id']) ? (int) $_GET['provider_id'] : null;

$sql = "SELECT c.id, c.status, c.notes, c.created_at, c.updated_at,
               CONCAT(u.first_name, ' ', u.last_name) AS provider_name,
               cl.name AS clinic_name
        FROM consultations c
        JOIN healthcare_providers hp ON hp.id = c.healthcare_provider_id
        JOIN users u ON u.id = hp.user_id
        LEFT JOIN clinics cl ON cl.id = hp.clinic_id
        WHERE c.caregiver_id = ?";

if ($providerId !== null) {
    $sql  .= ' AND c.healthcare_provider_id = ?';
    $sql  .= ' ORDER BY c.created_at DESC LIMIT 1';
    $stmt  = $db->prepare($sql);
    $stmt->bind_param('ii', $cgId, $providerId);
} else {
    $sql  .= ' ORDER BY c.created_at DESC LIMIT 1';
    $stmt  = $db->prepare($sql);
    $stmt->bind_param('i', $cgId);
}

$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    json_success(null);
}

json_success([
    'id'           => (int) $row['id'],
    'status'       => $row['status'],
    'notes'        => $row['notes'],
    'providerName' => $row['provider_name'],
    'clinicName'   => $row['clinic_name'],
    'createdAt'    => $row['created_at'],
    'updatedAt'    => $row['updated_at'],
]);
