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
$pending_result = $db->query(
    "SELECT
        u.id,
        CONCAT(u.first_name, ' ', u.last_name) AS full_name,
        u.email,
        u.phone_number,
        u.created_at AS date_applied,
        hp.specialization,
        hp.license_number,
        hp.prc_id_path,
        c.name               AS clinic_name,
        c.address,
        c.barangay,
        c.contact_number     AS clinic_contact,
        c.operating_hours,
        c.accepts_walk_ins,
        c.has_wheelchair_access,
        c.has_ground_floor_access,
        GROUP_CONCAT(
            DISTINCT CONCAT(cs.disability_category, ': ', cs.specific_condition)
            ORDER BY cs.id SEPARATOR '; '
        ) AS conditions_served
    FROM users u
    JOIN  healthcare_providers hp ON hp.user_id  = u.id
    LEFT JOIN clinics c               ON c.id       = hp.clinic_id
    LEFT JOIN clinic_specializations cs ON cs.clinic_id = c.id
    WHERE u.role = 'healthcare_provider' AND u.status = 'pending'
    GROUP BY u.id, u.phone_number,
             hp.specialization, hp.license_number, hp.prc_id_path,
             c.name, c.address, c.barangay, c.contact_number,
             c.operating_hours, c.accepts_walk_ins,
             c.has_wheelchair_access, c.has_ground_floor_access
    ORDER BY u.created_at DESC"
);
$pending_requests = $pending_result ? $pending_result->fetch_all(MYSQLI_ASSOC) : [];
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
    if (in_array($action, ['approve', 'reject'], true) && $user_id > 0) {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        $stmt = $db->prepare('UPDATE users SET status = ? WHERE id = ? AND role = "healthcare_provider"');
        $stmt->bind_param('si', $new_status, $user_id);
        $stmt->execute();
        $stmt->close();

        // Notify the provider
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

        // Audit log
        $admin_id    = (int) ($_SESSION['user_id'] ?? 0);
        $action_desc = $action === 'approve'
            ? "Approved healthcare provider registration (user_id: {$user_id})"
            : "Rejected healthcare provider registration (user_id: {$user_id})";
        $stmt = $db->prepare('INSERT INTO activity_logs (admin_id, action_description) VALUES (?, ?)');
        $stmt->bind_param('is', $admin_id, $action_desc);
        $stmt->execute();
        $stmt->close();

        $msg = $action === 'approve' ? 'Provider approved and notified.' : 'Provider registration rejected.';
        header('Location: dashboard_admin.php?notice=' . urlencode($msg));
        exit;
    }
}

// ── Nav items ────────────────────────────────────────────────
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => true],
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
<link rel="stylesheet" href="css/admin_dashboard.css">
<style>
/* ── View Button ─────────────────────────────────────── */
.btn-view { background: var(--teal-xlight); }
.btn-view:hover { background: var(--teal-light); }
.btn-view svg { fill: var(--teal-dark); }

/* ── Registration Detail Modal ───────────────────────── */
.modal-overlay {
  display: none; position: fixed; inset: 0; z-index: 1000;
  background: rgba(0,0,0,.48);
  align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.open { display: flex; }
.modal-box {
  background: var(--white); border-radius: var(--radius);
  width: 100%; max-width: 660px; max-height: 88vh;
  display: flex; flex-direction: column;
  box-shadow: var(--shadow-lg); overflow: hidden;
  animation: modal-in .18s ease;
}
@keyframes modal-in { from { opacity:0; transform:scale(.96) } to { opacity:1; transform:scale(1) } }
.modal-hdr {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; padding: 22px 28px 18px; border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}
.modal-hdr-left { flex: 1; min-width: 0; }
.modal-title { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 3px; }
.modal-subtitle { font-size: 13px; color: var(--text-muted); margin: 0; }
.modal-close-btn {
  width: 32px; height: 32px; border-radius: 8px; border: none;
  background: var(--teal-xlight); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: background .15s;
}
.modal-close-btn:hover { background: var(--teal-light); }
.modal-close-btn svg { width: 18px; height: 18px; fill: var(--text-mid); }
.modal-body {
  flex: 1; overflow-y: auto; padding: 22px 28px;
  display: flex; flex-direction: column; gap: 18px;
}
.modal-section { display: flex; flex-direction: column; gap: 10px; }
.modal-section-title {
  font-size: 10.5px; font-weight: 700; letter-spacing: .07em;
  color: var(--teal); text-transform: uppercase;
}
.modal-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; }
.modal-field-full { grid-column: 1 / -1; }
.modal-label {
  font-size: 10.5px; font-weight: 600; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px;
}
.modal-value { font-size: 13.5px; color: var(--text-dark); font-weight: 500; word-break: break-word; }
.modal-value.empty { color: var(--text-muted); font-style: italic; font-weight: 400; }
.modal-badge {
  display: inline-block; padding: 2px 9px; border-radius: 4px;
  font-size: 12px; font-weight: 600;
}
.badge-yes { background: #e6f9f0; color: #1a7a45; }
.badge-no  { background: var(--teal-xlight); color: var(--text-muted); }
.badge-uploaded { background: var(--teal-xlight); color: var(--teal-dark); }
.modal-hr { height: 1px; background: var(--border); }
.modal-footer {
  padding: 14px 28px; border-top: 1px solid var(--border);
  display: flex; justify-content: flex-end; flex-shrink: 0;
}
.btn-modal-close {
  padding: 8px 22px; border-radius: var(--radius-sm);
  font-family: var(--font); font-size: 13px; font-weight: 600;
  cursor: pointer; border: 1.5px solid var(--border);
  background: var(--white); color: var(--text-mid); transition: background .15s;
}
.btn-modal-close:hover { background: var(--bg); }
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="sidebar-brand">
      <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:56px;height:56px;object-fit:contain;">
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
                <th>Clinic / Practice</th>
                <th>License No.</th>
                <th>Specializations</th>
                <th>Address</th>
                <th>Date Applied</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_requests as $req): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($req['full_name'] ?? '') ?></strong>
                  <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($req['email'] ?? '') ?></div>
                </td>
                <td><?= htmlspecialchars($req['clinic_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($req['license_number'] ?? '—') ?></td>
                <td style="max-width:200px;white-space:normal;font-size:12px;"><?= htmlspecialchars($req['specializations'] ?? '—') ?></td>
                <td style="max-width:180px;white-space:normal;font-size:12px;">
                  <?= htmlspecialchars($req['address'] ?? '') ?>
                  <?php if (!empty($req['barangay'])): ?>
                    <span style="display:block;color:var(--text-muted);"><?= htmlspecialchars($req['barangay']) ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(isset($req['date_applied']) ? date('M j, Y', strtotime($req['date_applied'])) : '') ?></td>
                <td>
                  <div class="action-btns">
                    <button type="button" class="btn-icon btn-view" title="View Details"
                      onclick="openReqModal(<?= (int)($req['id'] ?? 0) ?>)">
                      <svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                    </button>
                    <button type="submit" name="action" value="approve"
                      class="btn-icon btn-approve" title="Approve"
                      onclick="this.form.user_id.value='<?= (int)($req['id'] ?? 0) ?>';return confirm('Approve this registration?')">
                      <svg viewBox="0 0 24 24" fill="#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                    </button>
                    <button type="submit" name="action" value="reject"
                      class="btn-icon btn-reject" title="Reject"
                      onclick="this.form.user_id.value='<?= (int)($req['id'] ?? 0) ?>';return confirm('Reject this registration?')">
                      <svg viewBox="0 0 24 24" fill="#fff"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
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
        <button class="action-btn btn-primary" onclick="window.location='user_management.php'">
          <svg viewBox="0 0 24 24" fill="#fff"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
          Manage Approved Users
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

<!-- ══ REGISTRATION DETAIL MODAL ══ -->
<div id="req-modal" class="modal-overlay" onclick="if(event.target===this)closeReqModal()">
  <div class="modal-box">
    <div class="modal-hdr">
      <div class="modal-hdr-left">
        <h2 class="modal-title" id="modal-name"></h2>
        <p class="modal-subtitle" id="modal-email"></p>
      </div>
      <button class="modal-close-btn" onclick="closeReqModal()" title="Close">
        <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
      </button>
    </div>
    <div class="modal-body" id="modal-body"></div>
    <div class="modal-footer">
      <button class="btn-modal-close" onclick="closeReqModal()">Close</button>
    </div>
  </div>
</div>

<script>
/* ── Pending request data (keyed by user id) ── */
const REQ_DATA = {};
<?php foreach ($pending_requests as $req): ?>
REQ_DATA[<?= (int)$req['id'] ?>] = <?= json_encode($req, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
<?php endforeach; ?>

function esc(v) {
  return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function val(v, fallback) {
  return (v !== null && v !== undefined && v !== '') ? v : (fallback ?? null);
}
function fieldHTML(label, value, full) {
  const empty = (value === null || value === undefined || value === '');
  const cls = full ? 'modal-field-full' : '';
  return `<div class="${cls}">
    <div class="modal-label">${esc(label)}</div>
    <div class="modal-value${empty ? ' empty' : ''}">${empty ? 'Not provided' : esc(value)}</div>
  </div>`;
}
function badgeField(label, flag, yesText, noText, full) {
  const isYes = (flag == 1 || flag === '1' || flag === true);
  const cls = full ? 'modal-field-full' : '';
  return `<div class="${cls}">
    <div class="modal-label">${esc(label)}</div>
    <div class="modal-value"><span class="modal-badge ${isYes ? 'badge-yes' : 'badge-no'}">${isYes ? (yesText||'Yes') : (noText||'No')}</span></div>
  </div>`;
}

function openReqModal(userId) {
  const r = REQ_DATA[userId];
  if (!r) return;

  const fullAddress = [r.address, r.barangay].filter(Boolean).join(', Brgy. ');
  const dateApplied = r.date_applied
    ? new Date(r.date_applied).toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'})
    : null;

  document.getElementById('modal-name').textContent  = r.full_name  || '—';
  document.getElementById('modal-email').textContent = r.email || '';

  document.getElementById('modal-body').innerHTML = `
    <div class="modal-section">
      <div class="modal-section-title">Personal Information</div>
      <div class="modal-fields">
        ${fieldHTML('Full Name',     r.full_name,    false)}
        ${fieldHTML('Phone Number',  r.phone_number, false)}
        ${fieldHTML('Email Address', r.email,        false)}
        ${fieldHTML('Date Applied',  dateApplied,    false)}
      </div>
    </div>

    <div class="modal-hr"></div>

    <div class="modal-section">
      <div class="modal-section-title">Credentials</div>
      <div class="modal-fields">
        ${fieldHTML('License Number', r.license_number, false)}
        <div>
          <div class="modal-label">PRC ID / Documents</div>
          <div class="modal-value">
            ${r.prc_id_path
              ? '<span class="modal-badge badge-uploaded">Uploaded</span>'
              : '<span class="modal-value empty">Not provided</span>'}
          </div>
        </div>
        ${fieldHTML('Specialization', r.specialization, true)}
      </div>
    </div>

    <div class="modal-hr"></div>

    <div class="modal-section">
      <div class="modal-section-title">Clinic / Practice</div>
      <div class="modal-fields">
        ${fieldHTML('Clinic Name',     r.clinic_name,    false)}
        ${fieldHTML('Contact Number',  r.clinic_contact, false)}
        ${fieldHTML('Address',         fullAddress || null, true)}
        ${fieldHTML('Operating Hours', r.operating_hours, true)}
      </div>
    </div>

    <div class="modal-hr"></div>

    <div class="modal-section">
      <div class="modal-section-title">Services &amp; Conditions Served</div>
      <div class="modal-fields">
        ${fieldHTML('Conditions Served', r.conditions_served, true)}
      </div>
    </div>

    <div class="modal-hr"></div>

    <div class="modal-section">
      <div class="modal-section-title">Accessibility Features</div>
      <div class="modal-fields">
        ${badgeField('Accepts Walk-ins',    r.accepts_walk_ins,         'Yes','No', false)}
        ${badgeField('Wheelchair Access',   r.has_wheelchair_access,    'Yes','No', false)}
        ${badgeField('Ground Floor Access', r.has_ground_floor_access,  'Yes','No', false)}
      </div>
    </div>
  `;

  document.getElementById('req-modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeReqModal() {
  document.getElementById('req-modal').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeReqModal();
});
</script>

</body>
</html>