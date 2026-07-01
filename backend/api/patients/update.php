<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$db        = get_db();
$caregiver = require_caregiver($db);

$body = get_json_body();
require_fields($body, ['id']);

$id = (int) $body['id'];

// Ownership check
$stmt = $db->prepare('SELECT id FROM patients WHERE id = ? AND caregiver_id = ? AND deleted_at IS NULL LIMIT 1');
$stmt->bind_param('ii', $id, $caregiver['caregiver_id']);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    json_error(404, 'Patient not found.');
}

$validCategories = ['physical', 'sensory_visual', 'sensory_hearing', 'cognitive'];
if (isset($body['disability_category']) && !in_array($body['disability_category'], $validCategories, true)) {
    json_error(422, 'Invalid disability_category.');
}
if (isset($body['gender']) && !in_array($body['gender'], ['male', 'female', 'other'], true)) {
    json_error(422, 'Invalid gender.');
}

$fieldsMap = [
    'first_name'          => 'first_name',
    'last_name'           => 'last_name',
    'date_of_birth'       => 'date_of_birth',
    'gender'              => 'gender',
    'disability_category' => 'disability_category',
    'specific_condition'  => 'specific_condition',
    'medical_history'     => 'medical_history',
];

$setClauses = [];
$params     = [];
$types      = '';
foreach ($fieldsMap as $bodyKey => $column) {
    if (array_key_exists($bodyKey, $body)) {
        $setClauses[] = "$column = ?";
        $params[]     = $body[$bodyKey];
        $types       .= 's';
    }
}

if (empty($setClauses)) {
    json_error(422, 'No updatable fields were provided.');
}

$params[] = $id;
$types   .= 'i';

$sql  = 'UPDATE patients SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();

json_success(['message' => 'Patient updated successfully.']);
