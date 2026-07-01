<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db        = get_db();
$caregiver = require_caregiver($db);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    json_error(422, 'A valid patient id is required.');
}

$stmt = $db->prepare(
    'SELECT id, first_name, last_name, date_of_birth, gender, disability_category, specific_condition, medical_history, created_at
     FROM patients
     WHERE id = ? AND caregiver_id = ? AND deleted_at IS NULL
     LIMIT 1'
);
$stmt->bind_param('ii', $id, $caregiver['caregiver_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    json_error(404, 'Patient not found.');
}

json_success([
    'id'                 => (int) $row['id'],
    'firstName'          => $row['first_name'],
    'lastName'           => $row['last_name'],
    'dateOfBirth'        => $row['date_of_birth'],
    'gender'             => $row['gender'],
    'disabilityCategory' => $row['disability_category'],
    'specificCondition'  => $row['specific_condition'],
    'medicalHistory'     => $row['medical_history'],
    'createdAt'          => $row['created_at'],
]);
