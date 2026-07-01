<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('GET');

$db        = get_db();
$caregiver = require_caregiver($db);

$stmt = $db->prepare(
    'SELECT id, first_name, last_name, date_of_birth, gender, disability_category, specific_condition, medical_history, created_at
     FROM patients
     WHERE caregiver_id = ? AND deleted_at IS NULL
     ORDER BY created_at DESC'
);
$stmt->bind_param('i', $caregiver['caregiver_id']);
$stmt->execute();
$result = $stmt->get_result();

$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = [
        'id'                 => (int) $row['id'],
        'firstName'          => $row['first_name'],
        'lastName'           => $row['last_name'],
        'dateOfBirth'        => $row['date_of_birth'],
        'gender'             => $row['gender'],
        'disabilityCategory' => $row['disability_category'],
        'specificCondition'  => $row['specific_condition'],
        'medicalHistory'     => $row['medical_history'],
        'createdAt'          => $row['created_at'],
    ];
}
$stmt->close();

json_success($patients);
