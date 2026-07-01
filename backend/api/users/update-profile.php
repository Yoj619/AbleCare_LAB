<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['data' => null, 'error' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['data' => null, 'error' => 'Method not allowed.']);
    exit;
}

$root = dirname(__DIR__, 3);
require_once $root . '/web-app/db.php';

$db     = get_db();
$userId = (int) $_SESSION['user_id'];
$action = trim($_POST['action'] ?? '');

// ── Update Profile ───────────────────────────────────────────────────────────
if ($action === 'update_profile') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $phone     = trim($_POST['contact_number'] ?? '');

    if ($firstName === '' || $lastName === '') {
        http_response_code(422);
        echo json_encode(['data' => null, 'error' => 'First and last name are required.']);
        exit;
    }

    // Handle profile picture upload
    $avatarUrl = null;
    if (!empty($_FILES['profile_picture']['tmp_name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $root . '/backend/uploads/profile-pictures/';
        $ext       = strtolower(pathinfo((string) $_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            http_response_code(422);
            echo json_encode(['data' => null, 'error' => 'Invalid file type. Allowed: jpg, png, gif, webp.']);
            exit;
        }
        if ((int) $_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
            http_response_code(422);
            echo json_encode(['data' => null, 'error' => 'File too large. Maximum 2 MB.']);
            exit;
        }

        $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
        if (move_uploaded_file((string) $_FILES['profile_picture']['tmp_name'], $uploadDir . $filename)) {
            $photoPath = 'backend/uploads/profile-pictures/' . $filename;
            $avatarUrl = '/AbleCare/' . $photoPath;

            $s = $db->prepare('UPDATE users SET profile_photo_path=? WHERE id=?');
            $s->bind_param('si', $photoPath, $userId);
            $s->execute();
            $s->close();

            $_SESSION['avatar'] = $avatarUrl;
        }
    }

    // Update core user fields
    $stmt = $db->prepare('UPDATE users SET first_name=?, last_name=?, email=?, phone_number=?, updated_at=NOW() WHERE id=?');
    $stmt->bind_param('ssssi', $firstName, $lastName, $email, $phone, $userId);
    $stmt->execute();
    $stmt->close();

    // Update provider-specific fields if present
    if (isset($_POST['specialization']) || isset($_POST['license_number'])) {
        $spec     = trim($_POST['specialization']  ?? '');
        $license  = trim($_POST['license_number']  ?? '');

        $chk = $db->prepare('SELECT id FROM healthcare_providers WHERE user_id=? LIMIT 1');
        $chk->bind_param('i', $userId);
        $chk->execute();
        $hpRow = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($hpRow) {
            $s = $db->prepare('UPDATE healthcare_providers SET specialization=?, license_number=? WHERE user_id=?');
            $s->bind_param('ssi', $spec, $license, $userId);
            $s->execute();
            $s->close();
        }
    }

    // Refresh session name/email
    $_SESSION['full_name'] = trim($firstName . ' ' . $lastName);
    $_SESSION['email']     = $email;

    $response = ['message' => 'Profile updated successfully.'];
    if ($avatarUrl !== null) {
        $response['avatar'] = $avatarUrl;
    }

    echo json_encode(['data' => $response, 'error' => null]);
    exit;
}

// ── Update Password ──────────────────────────────────────────────────────────
if ($action === 'update_password') {
    $currentPw = $_POST['current_password']  ?? '';
    $newPw     = $_POST['new_password']       ?? '';
    $confirmPw = $_POST['confirm_password']   ?? '';

    if ($currentPw === '' || $newPw === '' || $confirmPw === '') {
        http_response_code(422);
        echo json_encode(['data' => null, 'error' => 'All password fields are required.']);
        exit;
    }
    if ($newPw !== $confirmPw) {
        http_response_code(422);
        echo json_encode(['data' => null, 'error' => 'New password and confirmation do not match.']);
        exit;
    }
    if (strlen($newPw) < 8) {
        http_response_code(422);
        echo json_encode(['data' => null, 'error' => 'Password must be at least 8 characters.']);
        exit;
    }

    $stmt = $db->prepare('SELECT password FROM users WHERE id=? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($currentPw, (string) $row['password'])) {
        http_response_code(422);
        echo json_encode(['data' => null, 'error' => 'Current password is incorrect.']);
        exit;
    }

    $hash = password_hash($newPw, PASSWORD_BCRYPT);
    $stmt = $db->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?');
    $stmt->bind_param('si', $hash, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['data' => ['message' => 'Password updated successfully.'], 'error' => null]);
    exit;
}

http_response_code(422);
echo json_encode(['data' => null, 'error' => 'Unknown action.']);
