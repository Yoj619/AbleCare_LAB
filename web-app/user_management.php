<?php
// ============================================================
//  ABLECARE – User Management
//  Municipality of Nasugbu, Batangas
// ============================================================

session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'lgu_admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$db = get_db();

// ── Current logged-in admin ──
$admin = [
    'name'   => $_SESSION['full_name'] ?? 'Administrator',
    'role'   => $_SESSION['role'] ?? 'lgu_admin',
    'avatar' => $_SESSION['avatar'] ?? '',
];

// ── Handle Delete User ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int) $_POST['delete_id'];
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        header("Location: user_management.php?notice=User deleted successfully");
        exit;
    } else {
        header("Location: user_management.php?notice=You cannot delete your own account");
        exit;
    }
}

// ── Handle Role Update ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'], $_POST['user_id'], $_POST['new_role'])) {
    $user_id = (int) $_POST['user_id'];
    $new_role = $_POST['new_role'];
    if (in_array($new_role, ['lgu_admin', 'healthcare_provider'])) {
        // users.role enum stores 'admin' — the rest of the app uses 'lgu_admin'
        $db_role = $new_role === 'lgu_admin' ? 'admin' : $new_role;
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $db_role, $user_id);
        $stmt->execute();
        header("Location: user_management.php?notice=Role updated successfully");
        exit;
    }
}

// ── Handle Add Healthcare Provider ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_provider'])) {
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $email        = trim($_POST['provider_email'] ?? '');
    $contact      = trim($_POST['contact_number'] ?? '');
    $barangay     = trim($_POST['barangay'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $license      = trim($_POST['license_number'] ?? '');
    $temp_password = bin2hex(random_bytes(8));
    $hashed       = password_hash($temp_password, PASSWORD_DEFAULT);
    $role         = 'healthcare_provider';

    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed, $role);
    if ($stmt->execute()) {
        header("Location: user_management.php?notice=Healthcare provider added successfully");
    } else {
        header("Location: user_management.php?notice=Error adding provider");
    }
    exit;
}

// ── Get all users (except current admin) ──
$users_query = $db->query("
    SELECT id, first_name, last_name, email, role, created_at
    FROM users
    WHERE id != {$_SESSION['user_id']}
    ORDER BY created_at DESC
");

// ── Statistics ──
$total_users     = $db->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_admins    = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];
$total_providers = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'healthcare_provider'")->fetch_assoc()['total'];

// ── Greeting ──
$hour     = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$current_datetime = date('l, F j, Y • g:i A');

// ── Logout ──
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ── Nav items ──
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => false],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => true],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => false],
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => false],
    ['icon' => 'notifications',   'label' => 'System Notifications',   'href' => 'notifications.php',     'active' => false],
    ['icon' => 'reports',         'label' => 'Reports & Activity Log', 'href' => 'reports.php',           'active' => false],
    ['icon' => 'settings',        'label' => 'Account Settings',       'href' => 'settings.php',          'active' => false],
];

// ── SVG icons ──
$svg = [
    'dashboard'       => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
    'user-management' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
    'facilities'      => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H8v-2h4v2zm4-4H8v-2h8v2zm0-4H8V7h8v2z"/>',
    'emergency'       => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>',
    'notifications'   => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
    'reports'         => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
    'settings'        => '<path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.56-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.63-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.03-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>',
    'people'          => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
    'medical'         => '<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>',
    'bell'            => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
    'heart'           => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>',
    'shield'          => '<path d="M12 2L3 7v6c0 5.25 3.75 10.15 9 11.35C17.25 23.15 21 18.25 21 13V7L12 2zm-1 14l-3-3 1.41-1.41L11 13.17l4.59-4.58L17 10l-6 6z"/>',
    'logout'          => '<path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>',
    'clock'           => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 5v5.25l4.5 2.67-.75 1.23L11 13V7h1.5z"/>',
    'check'           => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>',
    'close'           => '<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>',
    'trend'           => '<path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>',
    'empty'           => '<path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.46 0 12.34 0c-1.7 0-3.23.64-4.37 1.68L7 3H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7.66-4c1.14 0 2.16.61 2.66 1.5H9.37l1.25-1.23A3.2 3.2 0 0112.34 2zM20 20H4V5h2.38L4.81 6.63A4.64 4.64 0 004 9.34C4 11.9 6.1 14 8.66 14c1.37 0 2.6-.6 3.45-1.55L13.2 11l1.5 1.5A4.97 4.97 0 0018 14c2.76 0 5-2.24 5-5 0-1.67-.82-3.15-2.07-4.06L20 6v14z"/>',
    'export'          => '<path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>',
    'plus'            => '<path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>',
];

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
<title>AbleCare – User Management</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/user_management.css">
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:50px;height:auto;">
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
      <h1>User Management</h1>
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
      <div class="greeting-org">User Management — Municipality of Nasugbu, Batangas</div>
      <div class="greeting-badges">
        <div class="gbadge">
          <div class="gbadge-label">Total Users</div>
          <div class="gbadge-val"><?= number_format($total_users) ?></div>
        </div>
        <div class="gbadge">
          <div class="gbadge-label">LGU Admins</div>
          <div class="gbadge-val"><?= number_format($total_admins) ?></div>
        </div>
        <div class="gbadge">
          <div class="gbadge-label">Providers</div>
          <div class="gbadge-val"><?= number_format($total_providers) ?></div>
        </div>
      </div>
    </div>

    <!-- Users Table Panel -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          System Users
        </div>
        <div class="panel-actions">
          <!-- Export Users Button -->
          <button class="btn-export" onclick="openModal('exportModal')">
            <?= icon('export', $svg) ?>
            Export Users
          </button>
          <!-- Add Healthcare Facilities Button -->
          <button class="btn-add-provider" onclick="openModal('addProviderModal')">
            <?= icon('plus', $svg) ?>
            Add Healthcare Provider
          </button>
        </div>
      </div>

      <?php if ($users_query && $users_query->num_rows > 0): ?>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Full Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Registered</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($user = $users_query->fetch_assoc()):
              $display_role = $user['role'] === 'admin' ? 'lgu_admin' : $user['role'];
            ?>
            <tr>
              <td><strong><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?></strong></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td>
                <form method="POST" style="display: inline-flex; gap: 5px; align-items: center;">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <select name="new_role" onchange="confirmRoleChange(event, this)">
                    <option value="lgu_admin" <?= $display_role === 'lgu_admin' ? 'selected' : '' ?>>LGU Admin</option>
                    <option value="healthcare_provider" <?= $display_role === 'healthcare_provider' ? 'selected' : '' ?>>Healthcare Provider</option>
                  </select>
                  <input type="hidden" name="update_role" value="1">
                </form>
              </td>
              <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Delete this user? This cannot be undone.')" style="display: inline;">
                  <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                  <button type="submit" class="btn-delete">Delete</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <div class="empty-state">
          <?= icon('empty', $svg) ?>
          <p>No users found.</p>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</div><!-- /main -->


<!-- ══════════════════════════════════════════
     MODAL: ADD HEALTHCARE PROVIDER
══════════════════════════════════════════ -->
<div class="modal-overlay" id="addProviderModal">
  <div class="modal" style="max-width: 560px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">Add Healthcare Provider</div>
        <div class="modal-subtitle">Fill in the details to add a new healthcare provider to the system.</div>
      </div>
      <button class="modal-close" onclick="closeModal('addProviderModal')" aria-label="Close">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
      </button>
    </div>

    <form method="POST" action="user_management.php">
      <input type="hidden" name="add_provider" value="1">
      <div class="modal-body">

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="first_name">First Name</label>
            <input class="form-input" type="text" id="first_name" name="first_name" placeholder="Enter first name" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="last_name">Last Name</label>
            <input class="form-input" type="text" id="last_name" name="last_name" placeholder="Enter last name" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="provider_email">Email Address</label>
          <input class="form-input" type="email" id="provider_email" name="provider_email" placeholder="provider@example.com" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="contact_number">Contact Number</label>
          <input class="form-input" type="text" id="contact_number" name="contact_number" placeholder="0912-345-6789">
        </div>

        <div class="form-group">
          <label class="form-label" for="barangay">Barangay</label>
          <input class="form-input" type="text" id="barangay" name="barangay" placeholder="Brgy. San Jose">
        </div>

        <div class="form-group">
          <label class="form-label" for="specialization">Specialization</label>
          <input class="form-input" type="text" id="specialization" name="specialization" placeholder="e.g., General Practitioner, Cardiologist">
        </div>

        <div class="form-group">
          <label class="form-label" for="license_number">License Number</label>
          <input class="form-input" type="text" id="license_number" name="license_number" placeholder="PRC License Number">
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('addProviderModal')">Cancel</button>
        <button type="submit" class="btn-primary">Add Provider</button>
      </div>
    </form>
  </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL: EXPORT USERS
══════════════════════════════════════════ -->
<div class="modal-overlay" id="exportModal">
  <div class="modal" style="max-width: 440px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">Export Users</div>
        <div class="modal-subtitle">Select the format to export user data:</div>
      </div>
      <button class="modal-close" onclick="closeModal('exportModal')" aria-label="Close">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
      </button>
    </div>

    <div class="modal-body">
      <div class="export-options">
        <button class="export-option" onclick="exportUsers('csv')">Export as CSV</button>
        <button class="export-option" onclick="exportUsers('xlsx')">Export as Excel (XLSX)</button>
        <button class="export-option" onclick="exportUsers('pdf')">Export as PDF</button>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn-cancel" onclick="closeModal('exportModal')">Cancel</button>
    </div>
  </div>
</div>


<script>
  // ── Role change confirmation ────────────
  function confirmRoleChange(e, select) {
    const newRole = select.options[select.selectedIndex].text;
    if (!confirm('Are you sure you want to change this user\'s role to ' + newRole + '?')) {
      // Revert to original selection
      const originalValue = select.dataset.original || select.value;
      for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value === originalValue && !select.options[i].selected) {
          select.selectedIndex = i;
          break;
        }
      }
      return;
    }
    select.form.submit();
  }

  // Store original value when user focuses the select
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select[name="new_role"]').forEach(sel => {
      sel.dataset.original = sel.value;
    });
  });

  // ── Modal helpers ──────────────────────
  function openModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  // Close modal when clicking the backdrop
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) closeModal(this.id);
    });
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.active').forEach(o => closeModal(o.id));
    }
  });

  // ── Export handler ─────────────────────
  function exportUsers(format) {
    closeModal('exportModal');
    window.location.href = 'export_users.php?format=' + format;
  }
</script>

</body>
</html>
