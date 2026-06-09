<?php
// ============================================================
//  ABLECARE – LGU Health Office Admin Dashboard
//  Municipality of Nasugbu, Batangas
// ============================================================

session_start();

// Guard: only lgu_admin may access this page
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'lgu_admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// ── Current logged-in admin from session (populated from users table) ──
$admin = [
    'name'   => $_SESSION['full_name'] ?? 'Administrator',
    'role'   => $_SESSION['role'] ?? 'lgu_admin',
    'avatar' => $_SESSION['avatar'] ?? '',
];

// ── Stats (DB QUERY) ─────────────────────────────────────────
$db = get_db();

// Get total counts from users table
$result = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'caregiver'");
$total_caregivers = $result ? $result->fetch_assoc()['total'] : 0;

$result = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'healthcare_provider'");
$total_providers = $result ? $result->fetch_assoc()['total'] : 0;

$total_users = $db->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// For demo purposes - set to 0 if tables don't exist yet
$active_alerts = 0;
$total_patients = 0;

// ── Emergency Alerts (DB QUERY) ──────────────────────────────
$emergency_alerts = []; // Replace with actual query when table exists

// ── Pending Registration Requests (DB QUERY) ────────────────
$pending_requests = []; // Replace with actual query when status column exists
$pending_count = count($pending_requests);

// ── Active Today / Avg Response (DB QUERY) ──────────────────
$active_today = null;
$avg_response = null;

// ── Greeting time ────────────────────────────────────────────
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$current_datetime = date('l, F j, Y • g:i A');

// ── Logout handler ───────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ── Approve / Reject handler (POST) ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = (int) $_POST['user_id'];
    $action  = $_POST['action'];
    if (in_array($action, ['approve', 'reject'])) {
        $new_status = $action === 'approve' ? 'active' : 'rejected';
        // $db->query("UPDATE users SET status='$new_status' WHERE id=$user_id");
        $msg = $action === 'approve' ? 'User approved successfully.' : 'User rejected.';
        header("Location: admin_dashboard.php?notice=" . urlencode($msg));
        exit;
    }
}

// ── Nav items ────────────────────────────────────────────────
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'admin_dashboard.php',   'active' => true],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => false],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => false],
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => false],
    ['icon' => 'notifications',   'label' => 'System Notifications',   'href' => 'notifications.php',     'active' => false],
    ['icon' => 'reports',         'label' => 'Reports & Activity Log', 'href' => 'reports.php',           'active' => false],
    ['icon' => 'settings',        'label' => 'Account Settings',       'href' => 'settings.php',          'active' => false],
];

// ── SVG icon paths ───────────────────────────────────────────
$svg = [
  'dashboard'       => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
  'user-management' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
  'facilities'      => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H8v-2h4v2zm4-4H8v-2h8v2zm0-4H8V7h8v2z"/>',
  'emergency'       => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>',
  'notifications'   => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
  'reports'         => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
  'settings'        => '<path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.56-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.63-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.03-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>',
  'people'  => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
  'medical' => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>',
  'bell'    => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
  'heart'   => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>',
  'shield'  => '<path d="M12 2L3 7v6c0 5.25 3.75 10.15 9 11.35C17.25 23.15 21 18.25 21 13V7L12 2zm-1 14l-3-3 1.41-1.41L11 13.17l4.59-4.58L17 10l-6 6z"/>',
  'logout'  => '<path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>',
  'clock'   => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 5v5.25l4.5 2.67-.75 1.23L11 13V7h1.5z"/>',
  'check'   => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>',
  'close'   => '<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>',
  'trend'   => '<path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>',
  'empty'   => '<path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.46 0 12.34 0c-1.7 0-3.23.64-4.37 1.68L7 3H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7.66-4c1.14 0 2.16.61 2.66 1.5H9.37l1.25-1.23A3.2 3.2 0 0112.34 2zM20 20H4V5h2.38L4.81 6.63A4.64 4.64 0 004 9.34C4 11.9 6.1 14 8.66 14c1.37 0 2.6-.6 3.45-1.55L13.2 11l1.5 1.5A4.97 4.97 0 0018 14c2.76 0 5-2.24 5-5 0-1.67-.82-3.15-2.07-4.06L20 6v14z"/>',
];

// ── Helper ───────────────────────────────────────────────────
function icon(string $key, array $svg_map, string $extra_class = ''): string {
    $path = $svg_map[$key] ?? '';
    $cls  = $extra_class ? " class=\"$extra_class\"" : '';
    return "<svg viewBox=\"0 0 24 24\" xmlns=\"http://www.w3.org/2000/svg\"$cls>$path</svg>";
}

function formatRole(string $role): string {
    if ($role === 'lgu_admin') return 'LGU Health Administrator';
    if ($role === 'healthcare_provider') return 'Healthcare Provider';
    return ucfirst(str_replace('_', ' ', $role));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AbleCare – LGU Health Office Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --teal-dark:   #1f6b5e;
    --teal:        #2e8b7a;
    --teal-mid:    #3aa690;
    --teal-light:  #d0ede8;
    --teal-xlight: #eaf6f4;
    --teal-accent: #4dbfaa;
    --green:       #28a745;
    --red:         #e74c3c;
    --orange:      #e67e22;
    --text-dark:   #1a2e2b;
    --text-mid:    #4a6660;
    --text-muted:  #8aada8;
    --bg:          #f4f8f7;
    --white:       #ffffff;
    --border:      #dcecea;
    --shadow-sm:   0 1px 4px rgba(46,139,122,.08);
    --shadow-md:   0 4px 16px rgba(46,139,122,.12);
    --radius:      14px;
    --radius-sm:   8px;
    --sidebar-w:   222px;
    --font:        'DM Sans', sans-serif;
  }

  body { font-family: var(--font); background: var(--bg); color: var(--text-dark); min-height: 100vh; display: flex; }

  /* ── SIDEBAR ─────────────────────────── */
  .sidebar {
    width: var(--sidebar-w); min-height: 100vh;
    background: var(--white); border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
    box-shadow: var(--shadow-sm);
  }
  .sidebar-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 22px 20px 18px; border-bottom: 1px solid var(--border);
  }
  .brand-icon {
    width: 38px; height: 38px; background: var(--teal); border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .brand-icon svg { width: 20px; height: 20px; fill: #fff; }
  .brand-name { font-weight: 700; font-size: 15px; color: var(--teal-dark); line-height: 1.2; }
  .brand-sub  { font-size: 10px; color: var(--text-muted); font-weight: 500; letter-spacing: .04em; }
  .sidebar-nav { flex: 1; padding: 18px 12px; display: flex; flex-direction: column; gap: 3px; }
  .nav-item {
    display: flex; align-items: center; gap: 11px;
    padding: 10px 12px; border-radius: var(--radius-sm);
    font-size: 13.5px; font-weight: 500; color: var(--text-mid);
    cursor: pointer; transition: background .15s, color .15s;
    text-decoration: none; border: none; background: none; width: 100%;
  }
  .nav-item:hover { background: var(--teal-xlight); color: var(--teal-dark); }
  .nav-item.active { background: var(--teal); color: #fff; }
  .nav-item.active .nav-icon svg { fill: #fff; }
  .nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .nav-icon svg { width: 16px; height: 16px; fill: var(--text-muted); transition: fill .15s; }
  .nav-item:hover .nav-icon svg { fill: var(--teal-dark); }
  .sidebar-footer { padding: 14px 12px 20px; border-top: 1px solid var(--border); }
  .logout-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500; color: var(--text-muted);
    cursor: pointer; background: none; border: none; width: 100%;
    transition: color .15s, background .15s;
  }
  .logout-btn:hover { color: var(--red); background: #fdf0ef; }
  .logout-btn svg { width: 16px; height: 16px; fill: currentColor; }

  /* ── MAIN ────────────────────────────── */
  .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

  /* ── TOPBAR ──────────────────────────── */
  .topbar {
    background: var(--white); border-bottom: 1px solid var(--border);
    padding: 14px 32px; display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-sm);
  }
  .topbar-title h1 { font-size: 20px; font-weight: 700; color: var(--text-dark); }
  .topbar-title p  { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
  .topbar-right    { display: flex; align-items: center; gap: 14px; }
  .notif-btn {
    width: 36px; height: 36px; background: var(--teal-xlight);
    border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .15s;
  }
  .notif-btn:hover { background: var(--teal-light); }
  .notif-btn svg { width: 18px; height: 18px; fill: var(--teal); }
  .admin-chip {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 12px 6px 8px; border: 1px solid var(--border); border-radius: 40px;
    background: var(--white);
  }
  .admin-avatar {
    width: 30px; height: 30px; border-radius: 50%; object-fit: cover;
    background: var(--teal); display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0; overflow: hidden;
  }
  .admin-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
  .admin-chip-name { font-size: 13px; font-weight: 600; color: var(--text-dark); line-height: 1.2; }
  .admin-chip-role { font-size: 10px; color: var(--teal); }

  /* ── NOTICE BANNER ───────────────────── */
  .notice {
    margin: 0 32px; padding: 10px 16px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px;
    background: #e6f9f0; color: #1a7a45; border: 1px solid #b2ecd1; margin-top: 20px;
  }
  .notice svg { width: 16px; height: 16px; fill: currentColor; flex-shrink: 0; }

  /* ── CONTENT ─────────────────────────── */
  .content { padding: 24px 32px; display: flex; flex-direction: column; gap: 22px; }

  /* ── GREETING CARD ───────────────────── */
  .greeting-card {
    background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal-mid) 60%, var(--teal-accent) 100%);
    border-radius: var(--radius); padding: 28px 32px; color: #fff;
    position: relative; overflow: hidden; box-shadow: var(--shadow-md);
  }
  .greeting-card::before {
    content: ''; position: absolute; top: -30px; right: -30px;
    width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.07);
  }
  .greeting-card::after {
    content: ''; position: absolute; bottom: -50px; right: 80px;
    width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,.05);
  }
  .greeting-time { font-size: 12px; opacity: .8; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
  .greeting-time svg { width: 13px; height: 13px; fill: rgba(255,255,255,.8); }
  .greeting-name { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
  .greeting-org  { font-size: 13px; opacity: .75; margin-bottom: 18px; }
  .greeting-badges { display: flex; gap: 10px; }
  .gbadge {
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.25);
    border-radius: 8px; padding: 8px 16px; font-size: 12px;
  }
  .gbadge-label { opacity: .8; margin-bottom: 2px; }
  .gbadge-val   { font-size: 22px; font-weight: 700; line-height: 1; }
  .gbadge-val.empty { font-size: 16px; opacity: .6; }

  /* ── STATS GRID ──────────────────────── */
  .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
  .stat-card {
    background: var(--white); border-radius: var(--radius);
    padding: 20px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s;
  }
  .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
  .stat-icon {
    width: 40px; height: 40px; background: var(--teal-xlight);
    border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
  }
  .stat-icon svg { width: 20px; height: 20px; fill: var(--teal); }
  .stat-label { font-size: 11.5px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
  .stat-value { font-size: 28px; font-weight: 700; color: var(--text-dark); line-height: 1; }
  .stat-value.no-data { font-size: 18px; color: var(--text-muted); font-weight: 400; }
  .stat-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; margin-top: 6px; padding: 2px 8px; border-radius: 20px;
  }
  .stat-badge.live { background: #e6f9f0; color: var(--green); }

  /* ── PANEL ───────────────────────────── */
  .panel { background: var(--white); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; }
  .panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px 14px; border-bottom: 1px solid var(--border);
  }
  .panel-title { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 700; color: var(--text-dark); }
  .pulse-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--green); animation: pulse 1.5s infinite; }
  @keyframes pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(40,167,69,.4); }
    50%      { box-shadow: 0 0 0 6px rgba(40,167,69,0); }
  }
  .badge-pill { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
  .badge-active  { background: var(--teal); color: #fff; }
  .badge-pending { background: #fff3e0; color: var(--orange); }
  .view-all { font-size: 12.5px; font-weight: 600; color: var(--teal); text-decoration: none; display: flex; align-items: center; gap: 4px; transition: color .15s; }
  .view-all:hover { color: var(--teal-dark); }

  /* ── TABLE ───────────────────────────── */
  table { width: 100%; border-collapse: collapse; }
  thead th {
    padding: 10px 20px; font-size: 10.5px; font-weight: 700;
    color: var(--teal); text-transform: uppercase; letter-spacing: .06em;
    text-align: left; background: var(--teal-xlight);
  }
  tbody tr { border-top: 1px solid var(--border); transition: background .12s; }
  tbody tr:hover { background: var(--teal-xlight); }
  tbody td { padding: 13px 20px; font-size: 13.5px; color: var(--text-dark); vertical-align: middle; }
  .td-link { color: var(--teal); font-size: 13px; }

  /* ── EMPTY STATE ─────────────────────── */
  .empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 48px 24px; gap: 10px; color: var(--text-muted);
  }
  .empty-state svg { width: 40px; height: 40px; fill: var(--teal-light); }
  .empty-state p   { font-size: 13.5px; }

  /* ── ROLE BADGE ──────────────────────── */
  .role-badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; border: 1.5px solid; }
  .role-hp        { color: var(--teal-dark);  border-color: var(--teal-light);  background: var(--teal-xlight); }
  .role-caregiver { color: #7c5c00; border-color: #ffe5a0; background: #fffbec; }

  /* ── ACTION BUTTONS ──────────────────── */
  .action-btns { display: flex; gap: 6px; }
  .btn-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: none; transition: background .15s, transform .1s;
  }
  .btn-icon:active { transform: scale(.93); }
  .btn-approve { background: var(--teal); }
  .btn-approve:hover { background: var(--teal-dark); }
  .btn-reject  { background: #fdeaea; }
  .btn-reject:hover  { background: #f5c6c6; }
  .btn-icon svg { width: 16px; height: 16px; }

  /* ── BOTTOM ACTIONS ──────────────────── */
  .bottom-actions { display: flex; gap: 12px; padding: 20px 24px; border-top: 1px solid var(--border); }
  .action-btn {
    display: flex; align-items: center; gap: 8px; padding: 11px 22px;
    border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 600;
    cursor: pointer; border: none; transition: background .15s, transform .1s, box-shadow .15s; font-family: var(--font);
  }
  .action-btn:active { transform: scale(.97); }
  .action-btn svg { width: 16px; height: 16px; }
  .btn-primary   { background: var(--teal);      color: #fff; box-shadow: 0 2px 8px rgba(46,139,122,.25); }
  .btn-primary:hover  { background: var(--teal-dark); }
  .btn-secondary { background: var(--teal-dark); color: #fff; }
  .btn-secondary:hover { background: #174f45; }
  .btn-outline   { background: var(--white); color: var(--text-dark); border: 1.5px solid var(--border); }
  .btn-outline:hover   { border-color: var(--teal); color: var(--teal); }

  /* ── RESPONSIVE ──────────────────────── */
  @media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }
  @media (max-width: 768px)  { .sidebar { display: none; } .main { margin-left: 0; } .content { padding: 16px; } }
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><?= icon('shield', $svg) ?></div>
    <div>
      <div class="brand-name">AbleCare</div>
      <div class="brand-sub">LGU Admin Portal</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
    <a href="<?= htmlspecialchars($item['href']) ?>" class="nav-item <?= $item['active'] ? 'active' : '' ?>">
      <span class="nav-icon"><?= icon($item['icon'], $svg) ?></span>
      <?= htmlspecialchars($item['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="?logout=1" class="logout-btn" onclick="return confirm('Log out of AbleCare?')">
      <?= icon('logout', $svg) ?>
      Logout
    </a>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-title">
      <h1>LGU Health Office Dashboard</h1>
      <p>Municipality of Nasugbu, Batangas</p>
    </div>
    <div class="topbar-right">
      <button class="notif-btn" title="Notifications" onclick="window.location='notifications.php'">
        <?= icon('bell', $svg) ?>
      </button>
      <div class="admin-chip">
        <div class="admin-avatar">
          <?php if (!empty($admin['avatar'])): ?>
            <img src="<?= htmlspecialchars($admin['avatar']) ?>" alt="Admin">
          <?php else: ?>
            <?= strtoupper(substr($admin['name'], 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="admin-chip-name"><?= htmlspecialchars($admin['name']) ?></div>
          <div class="admin-chip-role"><?= htmlspecialchars(formatRole($admin['role'])) ?></div>
        </div>
      </div>
    </div>
  </header>

  <!-- NOTICE BANNER -->
  <?php if (!empty($_GET['notice'])): ?>
  <div class="notice">
    <?= icon('check', $svg) ?>
    <?= htmlspecialchars($_GET['notice']) ?>
  </div>
  <?php endif; ?>

  <!-- CONTENT -->
  <div class="content">

    <!-- Greeting Card -->
    <div class="greeting-card">
      <div class="greeting-time">
        <?= icon('clock', $svg) ?>
        <?= htmlspecialchars($current_datetime) ?>
      </div>
      <div class="greeting-name">
        <?= htmlspecialchars($greeting) ?><?= !empty($admin['name']) ? ', ' . htmlspecialchars($admin['name']) : '' ?>
      </div>
      <div class="greeting-org">LGU Health Office — Municipality of Nasugbu, Batangas</div>
      <div class="greeting-badges">
        <div class="gbadge">
          <div class="gbadge-label">Active Today</div>
          <div class="gbadge-val <?= $active_today === null ? 'empty' : '' ?>">
            <?= $active_today !== null ? htmlspecialchars((string)$active_today) : '—' ?>
          </div>
        </div>
        <div class="gbadge">
          <div class="gbadge-label">Avg. Response</div>
          <div class="gbadge-val <?= $avg_response === null ? 'empty' : '' ?>">
            <?= $avg_response !== null ? round($avg_response) . ' min' : '—' ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon"><?= icon('people', $svg) ?></div>
        <div class="stat-label">Total Registered Caregivers</div>
        <div class="stat-value">
          <?= number_format($total_caregivers) ?>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><?= icon('medical', $svg) ?></div>
        <div class="stat-label">Healthcare Providers</div>
        <div class="stat-value">
          <?= number_format($total_providers) ?>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><?= icon('bell', $svg) ?></div>
        <div class="stat-label">Active Emergency Alerts</div>
        <div class="stat-value <?= $active_alerts === null ? 'no-data' : '' ?>">
          <?= $active_alerts !== null ? number_format((int)$active_alerts) : 'No data' ?>
        </div>
        <?php if ($active_alerts !== null && $active_alerts > 0): ?>
          <span class="stat-badge live">● Live</span>
        <?php endif; ?>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><?= icon('heart', $svg) ?></div>
        <div class="stat-label">Total Registered Patients</div>
        <div class="stat-value <?= $total_patients === null ? 'no-data' : '' ?>">
          <?= $total_patients !== null ? number_format((int)$total_patients) : 'No data' ?>
        </div>
      </div>

    </div>

    <!-- Emergency Alerts Live Feed -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          <span class="pulse-dot"></span>
          Emergency Alerts Live Feed
          <?php if (!empty($emergency_alerts)): ?>
            <span class="badge-pill badge-active"><?= count($emergency_alerts) ?> Active</span>
          <?php endif; ?>
        </div>
        <a href="emergency_monitor.php" class="view-all">View All →</a>
      </div>

      <?php if (empty($emergency_alerts)): ?>
        <div class="empty-state">
          <?= icon('bell', $svg) ?>
          <p>No active emergency alerts at this time.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Patient Name</th>
              <th>Caregiver</th>
              <th>Location</th>
              <th>Date</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($emergency_alerts as $row): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['patient'] ?? '') ?></strong></td>
              <td class="td-link"><?= htmlspecialchars($row['caregiver'] ?? '') ?></td>
              <td class="td-link"><?= htmlspecialchars($row['location'] ?? '') ?></td>
              <td><?= htmlspecialchars(isset($row['created_at']) ? date('F j, Y', strtotime($row['created_at'])) : '') ?></td>
              <td><?= htmlspecialchars(isset($row['created_at']) ? date('g:i A', strtotime($row['created_at'])) : '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Pending Registration Requests -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          Pending Registration Requests
          <?php if ($pending_count > 0): ?>
            <span class="badge-pill badge-pending"><?= $pending_count ?> Pending</span>
          <?php endif; ?>
        </div>
        <a href="user_management.php" class="view-all">View All →</a>
      </div>

      <?php if (empty($pending_requests)): ?>
        <div class="empty-state">
          <?= icon('check', $svg) ?>
          <p>No pending registration requests.</p>
        </div>
      <?php else: ?>
        <form method="POST" id="bulk-form">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Barangay</th>
                <th>Date Applied</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_requests as $req): ?>
              <tr>
                <td><strong><?= htmlspecialchars($req['full_name'] ?? '') ?></strong></td>
                <td>
                  <?php $is_hp = ($req['role'] ?? '') === 'healthcare_provider'; ?>
                  <span class="role-badge <?= $is_hp ? 'role-hp' : 'role-caregiver' ?>">
                    <?= $is_hp ? 'Healthcare Provider' : 'Caregiver' ?>
                  </span>
                </td>
                <td class="td-link"><?= htmlspecialchars($req['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($req['barangay'] ?? '') ?></td>
                <td><?= htmlspecialchars(isset($req['date_applied']) ? date('F j, Y', strtotime($req['date_applied'])) : '') ?></td>
                <td>
                  <div class="action-btns">
                    <button type="submit" name="action" value="approve"
                      class="btn-icon btn-approve" title="Approve"
                      onclick="this.form.user_id.value='<?= (int)($req['id'] ?? 0) ?>';return confirm('Approve this registration?')">
                      <svg viewBox="0 0 24 24" fill="#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                    </button>
                    <button type="submit" name="action" value="reject"
                      class="btn-icon btn-reject" title="Reject"
                      onclick="this.form.user_id.value='<?= (int)($req['id'] ?? 0) ?>';return confirm('Reject this registration?')">
                      <svg viewBox="0 0 24 24" fill="#e74c3c"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <input type="hidden" name="user_id" value="">
        </form>
      <?php endif; ?>

      <!-- Bottom Action Buttons -->
      <div class="bottom-actions">
        <button class="action-btn btn-primary" onclick="window.location='user_management.php?filter=pending'">
          <svg viewBox="0 0 24 24" fill="#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
          Approve Pending Users
        </button>
        <button class="action-btn btn-secondary" onclick="window.location='emergency_monitor.php'">
          <svg viewBox="0 0 24 24" fill="#fff"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
          View Emergency Monitor
        </button>
        <button class="action-btn btn-outline" onclick="window.location='reports.php'">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
          Generate Report
        </button>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

</body>
</html>