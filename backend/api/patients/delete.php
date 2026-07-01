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

$stmt = $db->prepare(
    'UPDATE patients SET deleted_at = NOW() WHERE id = ? AND caregiver_id = ? AND deleted_at IS NULL'
);
$stmt->bind_param('ii', $id, $caregiver['caregiver_id']);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    json_error(404, 'Patient not found.');
}

json_success(['message' => 'Patient deleted successfully.']);
