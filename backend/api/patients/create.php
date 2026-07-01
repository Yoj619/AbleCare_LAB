<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db        = get_db();
$caregiver = require_caregiver($db);

$body = get_json_body();
require_fields($body, ['first_name', 'last_name']);

$firstName  = trim((string) $body['first_name']);
$lastName   = trim((string) $body['last_name']);
$dob        = !empty($body['date_of_birth']) ? (string) $body['date_of_birth'] : null;
$gender     = !empty($body['gender']) ? (string) $body['gender'] : null;
$category   = !empty($body['disability_category']) ? (string) $body['disability_category'] : null;
$condition  = !empty($body['specific_condition']) ? (string) $body['specific_condition'] : null;
$history    = !empty($body['medical_history']) ? (string) $body['medical_history'] : null;

$validCategories = ['physical', 'sensory_visual', 'sensory_hearing', 'cognitive'];
if ($category !== null && !in_array($category, $validCategories, true)) {
    json_error(422, 'Invalid disability_category.');
}
if ($gender !== null && !in_array($gender, ['male', 'female', 'other'], true)) {
    json_error(422, 'Invalid gender.');
}

$stmt = $db->prepare(
    'INSERT INTO patients (caregiver_id, first_name, last_name, date_of_birth, gender, disability_category, specific_condition, medical_history)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'isssssss',
    $caregiver['caregiver_id'], $firstName, $lastName, $dob, $gender, $category, $condition, $history
);
$stmt->execute();
$patientId = $stmt->insert_id;
$stmt->close();

json_success(['id' => $patientId], 201);
