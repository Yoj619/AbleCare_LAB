<?php
// ============================================================
//  ABLECARE – Account Settings
//  Municipality of Nasugbu, Batangas
// ============================================================

session_start();

// ── Session guard (uncomment when DB is ready) ──
// if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'lgu_admin') {
//     header('Location: login.php');
//     exit;
// }

// ── Current logged-in admin (mock for front-end) ──
$admin = [
    'name'   => $_SESSION['full_name'] ?? 'Maria Elena Santos',
    'role'   => $_SESSION['role']      ?? 'lgu_admin',
    'avatar' => $_SESSION['avatar']    ?? '',
];

// ── Nav items ──
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => false],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => false],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => false],
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => false],
    ['icon' => 'notifications',   'label' => 'System Notifications',   'href' => 'notifications.php',     'active' => false],
    ['icon' => 'reports',         'label' => 'Reports & Activity Log', 'href' => 'reports.php',           'active' => false],
    ['icon' => 'settings',        'label' => 'Account Settings',       'href' => 'settings.php',          'active' => true],
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
    'bell'            => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
    'shield'          => '<path d="M12 2L3 7v6c0 5.25 3.75 10.15 9 11.35C17.25 23.15 21 18.25 21 13V7L12 2zm-1 14l-3-3 1.41-1.41L11 13.17l4.59-4.58L17 10l-6 6z"/>',
    'logout'          => '<path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>',
    'person'          => '<path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>',
    'lock'            => '<path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>',
    'eye'             => '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>',
    'eye-off'         => '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>',
    'camera'          => '<path d="M12 15.2A3.2 3.2 0 1 1 12 8.8a3.2 3.2 0 0 1 0 6.4zm7.2-10.4h-2.28L15.2 3H8.8L7.08 4.8H4.8A1.6 1.6 0 0 0 3.2 6.4v11.2A1.6 1.6 0 0 0 4.8 19.2h14.4a1.6 1.6 0 0 0 1.6-1.6V6.4a1.6 1.6 0 0 0-1.6-1.6z"/>',
    'back'            => '<path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>',
    'close'           => '<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>',
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

$admin_initials = strtoupper(substr($admin['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AbleCare – Account Settings</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/settings.css">
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
    <a href="logout.php" class="logout-btn" onclick="return confirm('Log out of AbleCare?')">
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
      <button class="notif-btn" title="Notifications">
        <?= icon('bell', $svg) ?>
      </button>
      <div class="admin-chip">
        <div class="admin-avatar">
          <?php if (!empty($admin['avatar'])): ?>
            <img src="<?= htmlspecialchars($admin['avatar']) ?>" alt="avatar">
          <?php else: ?>
            <?= $admin_initials ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="admin-chip-name"><?= htmlspecialchars($admin['name']) ?></div>
          <div class="admin-chip-role"><?= formatRole($admin['role']) ?></div>
        </div>
      </div>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- Page Header -->
    <div>
      <div class="page-header">
        <div class="page-header-icon"><?= icon('settings', $svg) ?></div>
        <div class="page-header-title">
          <h2>Account Settings</h2>
        </div>
      </div>
      <div class="breadcrumb">
        <a href="dashboard_admin.php">Dashboard</a> › Account Settings
      </div>
    </div>

    <!-- ── PROFILE PICTURE ── -->
    <div class="settings-card">
      <div class="settings-card-header">
        <div class="settings-card-icon"><?= icon('camera', $svg) ?></div>
        <div>
          <div class="settings-card-title">Profile Picture</div>
          <div class="settings-card-subtitle">Click the camera icon to update your profile photo</div>
        </div>
      </div>
      <div class="settings-card-body">
        <!-- Hidden file input -->
        <input type="file" id="profilePicInput" accept="image/*" style="display:none;" onchange="handleProfilePicChange(event)">

        <div class="profile-pic-wrapper">
          <!-- Circle with camera badge -->
          <div class="profile-pic-container">
            <div class="profile-pic-circle" id="profilePicCircle">
              <?php if (!empty($admin['avatar'])): ?>
                <img src="<?= htmlspecialchars($admin['avatar']) ?>" alt="Profile" id="profilePicPreview" class="profile-pic-img">
              <?php else: ?>
                <span class="profile-pic-initials" id="profilePicInitials"><?= $admin_initials ?></span>
                <img src="" alt="Profile" id="profilePicPreview" class="profile-pic-img" style="display:none;">
              <?php endif; ?>
            </div>
            <!-- Camera button -->
            <button type="button" class="profile-cam-btn" id="camBtn" onclick="togglePicMenu(event)" aria-label="Change profile picture">
              <?= icon('camera', $svg) ?>
            </button>
            <!-- Dropdown -->
            <div class="profile-pic-menu" id="profilePicMenu">
              <button type="button" class="pic-menu-item" onclick="openViewProfileModal()">
                <?= icon('eye', $svg) ?> View Profile
              </button>
              <button type="button" class="pic-menu-item" onclick="triggerPicInput()">
                <?= icon('camera', $svg) ?> Change Profile
              </button>
              <button type="button" class="pic-menu-item pic-menu-back" onclick="closePicMenu()">
                <?= icon('back', $svg) ?> Back
              </button>
            </div>
          </div>

          <!-- Info beside photo -->
          <div class="profile-pic-info">
            <div class="profile-pic-name"><?= htmlspecialchars($admin['name']) ?></div>
            <div class="profile-pic-role"><?= formatRole($admin['role']) ?></div>
            <div class="profile-pic-office">
              <?= icon('facilities', $svg) ?>
              Municipality of Nasugbu, Batangas
            </div>
            <div class="profile-pic-hint">Supported: JPG, PNG, GIF &bull; Max size: 5MB</div>
          </div>
        </div>
      </div>
    </div>

    <!-- View Profile Modal -->
    <div class="modal-overlay" id="modalViewProfile">
      <div class="modal-box">
        <button class="modal-x" onclick="closeModal('modalViewProfile')">&times;</button>
        <div class="modal-profile-head">
          <div class="modal-pic-circle" id="modalPicCircle">
            <?php if (!empty($admin['avatar'])): ?>
              <img src="<?= htmlspecialchars($admin['avatar']) ?>" alt="Profile" id="modalPicImg" class="modal-pic-img">
            <?php else: ?>
              <span class="modal-pic-initials" id="modalPicInitials"><?= $admin_initials ?></span>
              <img src="" alt="Profile" id="modalPicImg" class="modal-pic-img" style="display:none;">
            <?php endif; ?>
          </div>
          <div class="modal-profile-name"><?= htmlspecialchars($admin['name']) ?></div>
          <div class="modal-profile-role"><?= formatRole($admin['role']) ?></div>
        </div>
        <div class="modal-profile-details">
          <div class="mpd-row"><span class="mpd-label">Office</span><span class="mpd-value">Municipality of Nasugbu, Batangas</span></div>
          <div class="mpd-row"><span class="mpd-label">Email</span><span class="mpd-value">maria.santos@nasugbu.gov.ph</span></div>
          <div class="mpd-row"><span class="mpd-label">Contact</span><span class="mpd-value">0965-457-1094</span></div>
        </div>
        <div class="modal-footer">
          <button class="btn-primary" onclick="closeModal('modalViewProfile')" style="flex:unset; padding:10px 32px;">Close</button>
        </div>
      </div>
    </div>

    <!-- ── PROFILE INFORMATION ── -->
    <div class="settings-card">
      <div class="settings-card-header">
        <div class="settings-card-icon"><?= icon('person', $svg) ?></div>
        <div>
          <div class="settings-card-title">Profile Information</div>
        </div>
      </div>
      <form method="POST" action="settings.php" id="profileForm">
        <input type="hidden" name="action" value="update_profile">
        <div class="settings-card-body">

          <div id="profileNotice" class="notice-success">
            <?= icon('check', $svg) ?> Profile updated successfully.
          </div>

          <div class="form-row">
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
              <input class="form-input" type="text" id="full_name" name="full_name"
                     value="<?= htmlspecialchars($admin['name']) ?>"
                     placeholder="Your full name">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label" for="designation">Designation <span class="required">*</span></label>
              <input class="form-input" type="text" id="designation" name="designation"
                     value="<?= htmlspecialchars(formatRole($admin['role'])) ?>"
                     placeholder="e.g., LGU Health Administrator">
            </div>
          </div>

          <div class="form-row" style="margin-top:18px;">
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label" for="lgu_office">LGU Office <span class="required">*</span></label>
              <input class="form-input" type="text" id="lgu_office" name="lgu_office"
                     value="Municipality of Nasugbu"
                     placeholder="Your office / unit">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label" for="contact_number">Contact Number <span class="required">*</span></label>
              <input class="form-input" type="text" id="contact_number" name="contact_number"
                     value="0965-457-1094"
                     placeholder="0912-345-6789">
            </div>
          </div>

          <div class="form-group" style="margin-top:18px; margin-bottom:0;">
            <label class="form-label" for="email_address">Email Address <span class="required">*</span></label>
            <input class="form-input" type="email" id="email_address" name="email_address"
                   value="maria.santos@nasugbu.gov.ph"
                   placeholder="you@example.com">
          </div>

        </div>
        <div class="settings-card-footer">
          <button type="button" class="btn-primary" onclick="submitProfile(event)">
            <?= icon('check', $svg) ?> Save Changes
          </button>
          <button type="button" class="btn-cancel" onclick="resetProfile()">
            <?= icon('close', $svg) ?> Cancel
          </button>
        </div>
      </form>
    </div>

    <!-- ── SECURITY SETTINGS ── -->
    <div class="settings-card">
      <div class="settings-card-header">
        <div class="settings-card-icon"><?= icon('lock', $svg) ?></div>
        <div>
          <div class="settings-card-title">Security Settings</div>
          <div class="settings-card-subtitle">Update your password to keep your account secure</div>
        </div>
      </div>
      <form method="POST" action="settings.php" id="passwordForm">
        <input type="hidden" name="action" value="update_password">
        <div class="settings-card-body">

          <div id="passwordNotice" class="notice-success">
            <?= icon('check', $svg) ?> Password updated successfully.
          </div>

          <div class="form-group">
            <label class="form-label" for="current_password">Current Password <span class="required">*</span></label>
            <div class="input-wrap">
              <input class="form-input" type="password" id="current_password" name="current_password"
                     placeholder="Enter current password">
              <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)" title="Show/hide">
                <?= icon('eye', $svg) ?>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="new_password">New Password <span class="required">*</span></label>
            <div class="input-wrap">
              <input class="form-input" type="password" id="new_password" name="new_password"
                     placeholder="Enter new password">
              <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)" title="Show/hide">
                <?= icon('eye', $svg) ?>
              </button>
            </div>
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="confirm_password">Confirm New Password <span class="required">*</span></label>
            <div class="input-wrap">
              <input class="form-input" type="password" id="confirm_password" name="confirm_password"
                     placeholder="Confirm new password">
              <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)" title="Show/hide">
                <?= icon('eye', $svg) ?>
              </button>
            </div>
          </div>

        </div>
        <div class="settings-card-footer">
          <button type="button" class="btn-primary" onclick="submitPassword(event)">
            <?= icon('lock', $svg) ?> Update Password
          </button>
          <button type="button" class="btn-cancel" onclick="document.getElementById('passwordForm').reset()">
            <?= icon('close', $svg) ?> Cancel
          </button>
        </div>
      </form>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<script>
  // ── Profile Picture ──────────────────────
  function togglePicMenu(e) {
    e.stopPropagation();
    document.getElementById('profilePicMenu').classList.toggle('open');
  }
  function closePicMenu() {
    document.getElementById('profilePicMenu').classList.remove('open');
  }
  function triggerPicInput() {
    closePicMenu();
    document.getElementById('profilePicInput').click();
  }
  function handleProfilePicChange(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      alert('File is too large. Maximum allowed size is 5MB.');
      event.target.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      const src = e.target.result;
      // Main preview
      const prev = document.getElementById('profilePicPreview');
      const init = document.getElementById('profilePicInitials');
      prev.src = src; prev.style.display = 'block';
      if (init) init.style.display = 'none';
      // Modal preview
      const mImg = document.getElementById('modalPicImg');
      const mInit = document.getElementById('modalPicInitials');
      mImg.src = src; mImg.style.display = 'block';
      if (mInit) mInit.style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
  function openViewProfileModal() {
    closePicMenu();
    document.getElementById('modalViewProfile').classList.add('open');
  }
  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
  }
  // Close menu/modal on outside click
  document.addEventListener('click', function(e) {
    const menu = document.getElementById('profilePicMenu');
    const btn  = document.getElementById('camBtn');
    if (menu && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
      menu.classList.remove('open');
    }
  });
  document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
  });
  function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
      ? `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>`
      : `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>`;
  }

  // ── Profile form submit (front-end demo) ─
  function submitProfile(e) {
    e.preventDefault();
    const notice = document.getElementById('profileNotice');
    notice.classList.add('show');
    setTimeout(() => notice.classList.remove('show'), 3500);
    // TODO: replace with fetch/AJAX when DB is ready
  }

  function resetProfile() {
    document.getElementById('profileForm').reset();
    document.getElementById('profileNotice').classList.remove('show');
  }

  // ── Password form submit (front-end demo) ─
  function submitPassword(e) {
    e.preventDefault();
    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('confirm_password').value;
    if (np !== cp) {
      alert('New password and confirmation do not match.');
      return;
    }
    if (np.length < 8) {
      alert('Password must be at least 8 characters.');
      return;
    }
    const notice = document.getElementById('passwordNotice');
    notice.classList.add('show');
    document.getElementById('passwordForm').reset();
    setTimeout(() => notice.classList.remove('show'), 3500);
    // TODO: replace with fetch/AJAX when DB is ready
  }
</script>

</body>
</html>