<?php
declare(strict_types=1);

// ============================================================
//  AbleCare – Healthcare Provider Registration
//  POST multipart/form-data
//  Creates: users (pending) + clinics + healthcare_providers + clinic_specializations
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
    exit;
}

require_once dirname(__DIR__, 2) . '/db.php';

$BARANGAYS            = require dirname(__DIR__, 2) . '/constants/barangays.php';
$DISABILITY_CATEGORIES = require dirname(__DIR__, 2) . '/constants/disability_categories.php';

function jsonFail(int $status, array $errors): never {
    http_response_code($status);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

function uploadDir(): string {
    return dirname(__DIR__, 2) . '/uploads';
}

/** Moves an uploaded file into $subdir under /backend/uploads, returns the stored relative path or null. */
function storeUpload(string $field, string $label, string $subdir, array $allowedExt, int $maxBytes, bool $required, array &$errors): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $errors[] = $label . ' is required.';
        }
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload failed for ' . $label . '.';
        return null;
    }

    if ($file['size'] > $maxBytes) {
        $errors[] = $label . ' exceeds the maximum file size.';
        return null;
    }

    $ext = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $errors[] = $label . ' must be one of: ' . implode(', ', $allowedExt) . '.';
        return null;
    }

    $destDir = uploadDir() . '/' . $subdir;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = $destDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = 'Could not save uploaded file for ' . $field . '.';
        return null;
    }

    return 'uploads/' . $subdir . '/' . $filename;
}

// ─── Collect fields ───────────────────────────────────────────────────────────
$first_name      = trim((string) ($_POST['first_name'] ?? ''));
$last_name       = trim((string) ($_POST['last_name'] ?? ''));
$email           = trim((string) ($_POST['email'] ?? ''));
$password        = (string) ($_POST['password'] ?? '');
$confirm_password = (string) ($_POST['confirm_password'] ?? '');
$phone_number    = trim((string) ($_POST['phone_number'] ?? ''));

$clinic_name     = trim((string) ($_POST['clinic_name'] ?? ''));
$license_number  = trim((string) ($_POST['license_number'] ?? ''));

$address         = trim((string) ($_POST['address'] ?? ''));
$barangay        = trim((string) ($_POST['barangay'] ?? ''));
$operating_hours = trim((string) ($_POST['operating_hours'] ?? ''));
$accepts_walk_ins = (($_POST['accepts_walk_ins'] ?? '0') === '1') ? 1 : 0;
$wheelchair_accessible = (($_POST['wheelchair_accessible'] ?? '0') === '1') ? 1 : 0;
$ground_floor_access   = (($_POST['ground_floor_access'] ?? '0') === '1') ? 1 : 0;

$specializationsRaw = (string) ($_POST['specializations'] ?? '');
$specializations     = json_decode($specializationsRaw, true);

// ─── Validate ─────────────────────────────────────────────────────────────────
$errors = [];

if ($first_name === '') $errors[] = 'First name is required.';
if ($last_name === '')  $errors[] = 'Last name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

if ($clinic_name === '')    $errors[] = 'Clinic / practice name is required.';
if ($license_number === '') $errors[] = 'License number is required.';

if ($address === '')  $errors[] = 'Address is required.';
if ($barangay === '' || !in_array($barangay, $BARANGAYS, true)) {
    $errors[] = 'Please select a valid barangay.';
}

if (!is_array($specializations) || count($specializations) < 1) {
    $errors[] = 'At least one specialization is required.';
    $specializations = [];
}

$cleanSpecializations = [];
foreach ($specializations as $i => $spec) {
    $category  = trim((string) ($spec['disability_category'] ?? ''));
    $condition = trim((string) ($spec['specific_condition'] ?? ''));
    if (!array_key_exists($category, $DISABILITY_CATEGORIES)) {
        $errors[] = 'Specialization #' . ($i + 1) . ' has an invalid disability category.';
        continue;
    }
    if ($condition === '') {
        $errors[] = 'Specialization #' . ($i + 1) . ' is missing a specific condition.';
        continue;
    }
    $cleanSpecializations[] = ['disability_category' => $category, 'specific_condition' => $condition];
}

// Email uniqueness
if (empty($errors)) {
    $db = get_db();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = 'An account with that email already exists.';
    }
    $stmt->close();
}

if (!empty($errors)) {
    jsonFail(422, $errors);
}

// ─── Handle file uploads (after field validation passes) ─────────────────────
$profile_photo_path = storeUpload('profile_photo', 'Profile photo', 'profile_photos', ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024, false, $errors);
$prc_id_path         = storeUpload('prc_id', 'PRC ID', 'prc_ids', ['jpg', 'jpeg', 'png', 'pdf'], 10 * 1024 * 1024, true, $errors);

if (!empty($errors)) {
    foreach ([$profile_photo_path, $prc_id_path] as $path) {
        $absPath = dirname(uploadDir()) . '/' . $path;
        if ($path !== null && file_exists($absPath)) {
            unlink($absPath);
        }
    }
    jsonFail(422, $errors);
}

// ─── Persist (transactional) ──────────────────────────────────────────────────
$db = get_db();
$db->begin_transaction();

try {
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare(
        'INSERT INTO clinics (name, address, barangay, contact_number, operating_hours, accepts_walk_ins, has_wheelchair_access, has_ground_floor_access)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sssssiii',
        $clinic_name, $address, $barangay, $phone_number, $operating_hours,
        $accepts_walk_ins, $wheelchair_accessible, $ground_floor_access
    );
    $stmt->execute();
    $clinic_id = $stmt->insert_id;
    $stmt->close();

    $role = 'healthcare_provider';
    $stmt = $db->prepare(
        'INSERT INTO users (role, first_name, last_name, email, password, phone_number, profile_photo_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, "pending")'
    );
    $stmt->bind_param('sssssss', $role, $first_name, $last_name, $email, $hash, $phone_number, $profile_photo_path);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare(
        'INSERT INTO healthcare_providers (user_id, license_number, prc_id_path, clinic_id) VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('issi', $user_id, $license_number, $prc_id_path, $clinic_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $db->prepare(
        'INSERT INTO clinic_specializations (clinic_id, disability_category, specific_condition) VALUES (?, ?, ?)'
    );
    foreach ($cleanSpecializations as $spec) {
        $stmt->bind_param('iss', $clinic_id, $spec['disability_category'], $spec['specific_condition']);
        $stmt->execute();
    }
    $stmt->close();

    $db->commit();
} catch (\Throwable $e) {
    $db->rollback();
    foreach ([$profile_photo_path, $prc_id_path] as $path) {
        $absPath = dirname(uploadDir()) . '/' . $path;
        if ($path !== null && file_exists($absPath)) {
            unlink($absPath);
        }
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'errors' => ['Registration failed. Please try again.']]);
    exit;
}

http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Your registration has been submitted. Please wait for admin approval before you can log in.',
]);
