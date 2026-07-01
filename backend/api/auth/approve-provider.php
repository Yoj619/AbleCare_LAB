<?php
declare(strict_types=1);

// ============================================================
//  AbleCare – Approve or Reject a Healthcare Provider
//  POST application/json
//  Body: { "user_id": int, "action": "approve"|"reject" }
//  Auth: Bearer token (admin only)
// ============================================================

require_once dirname(__DIR__, 2) . '/helpers.php';

send_cors_headers();
require_method('POST');

$body = get_json_body();
require_fields($body, ['user_id', 'action']);

$user_id = (int) $body['user_id'];
$action  = (string) $body['action'];

if (!in_array($action, ['approve', 'reject'], true) || $user_id < 1) {
    json_error(422, 'action must be "approve" or "reject" and user_id must be a positive integer.');
}

// ── Auth: require a valid admin token ────────────────────────
$db    = get_db();
$admin = require_auth($db);

if ($admin['role'] !== 'admin') {
    json_error(403, 'Admin access required.');
}

// ── Verify target is a pending healthcare provider ────────────
$stmt = $db->prepare('SELECT id, status FROM users WHERE id = ? AND role = "healthcare_provider" LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$provider) {
    json_error(404, 'Healthcare provider not found.');
}
if ($provider['status'] !== 'pending') {
    json_error(409, 'This registration has already been reviewed (status: ' . $provider['status'] . ').');
}

// ── Update status ─────────────────────────────────────────────
$new_status = $action === 'approve' ? 'approved' : 'rejected';

$stmt = $db->prepare('UPDATE users SET status = ? WHERE id = ?');
$stmt->bind_param('si', $new_status, $user_id);
$stmt->execute();
$stmt->close();

// ── Notify the provider ───────────────────────────────────────
if ($action === 'approve') {
    $notif_title   = 'Account Approved';
    $notif_message = 'Your account has been approved. You can now log in.';
} else {
    $notif_title   = 'Registration Not Approved';
    $notif_message = 'Your registration was not approved. Please contact the administrator for more information.';
}
$notif_type = 'system';

$stmt = $db->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
$stmt->bind_param('isss', $user_id, $notif_title, $notif_message, $notif_type);
$stmt->execute();
$stmt->close();

// ── Audit log ─────────────────────────────────────────────────
$admin_id    = (int) $admin['id'];
$action_desc = $action === 'approve'
    ? "Approved healthcare provider registration (user_id: {$user_id})"
    : "Rejected healthcare provider registration (user_id: {$user_id})";

$stmt = $db->prepare('INSERT INTO activity_logs (admin_id, action_description) VALUES (?, ?)');
$stmt->bind_param('is', $admin_id, $action_desc);
$stmt->execute();
$stmt->close();

json_success(['message' => $action === 'approve' ? 'Provider approved.' : 'Provider rejected.']);
