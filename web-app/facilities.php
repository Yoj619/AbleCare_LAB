<?php
// ============================================================
//  ABLECARE – Healthcare Facilities
//  Municipality of Nasugbu, Batangas
// ============================================================

session_start();

// ── Session guard (uncomment when DB is ready) ──
// if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'lgu_admin') {
//     header('Location: login.php');
//     exit;
// }

// ── Current logged-in admin (session-based, falls back to defaults until DB is wired) ──
$admin = [
    'name'   => $_SESSION['full_name'] ?? 'Administrator',
    'role'   => $_SESSION['role']      ?? 'lgu_admin',
    'avatar' => $_SESSION['avatar']    ?? '',
];

// ── Healthcare facilities list ──
$facilities = []; // TODO: replace with DB query (SELECT * FROM facilities) when table exists

// ── Greeting ──
$hour     = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$current_datetime = date('l, F j, Y • g:i A');

// ── Nav items ──
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => false],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => false],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => true],
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
    'bell'            => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
    'shield'          => '<path d="M12 2L3 7v6c0 5.25 3.75 10.15 9 11.35C17.25 23.15 21 18.25 21 13V7L12 2zm-1 14l-3-3 1.41-1.41L11 13.17l4.59-4.58L17 10l-6 6z"/>',
    'logout'          => '<path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>',
    'clock'           => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 5v5.25l4.5 2.67-.75 1.23L11 13V7h1.5z"/>',
    'check'           => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>',
    'close'           => '<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>',
    'export'          => '<path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>',
    'plus'            => '<path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>',
    'eye'             => '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>',
    'edit'            => '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>',
    'delete'          => '<path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>',
    'empty'           => '<path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.46 0 12.34 0c-1.7 0-3.23.64-4.37 1.68L7 3H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7.66-4c1.14 0 2.16.61 2.66 1.5H9.37l1.25-1.23A3.2 3.2 0 0112.34 2zM20 20H4V5h2.38L4.81 6.63A4.64 4.64 0 004 9.34C4 11.9 6.1 14 8.66 14c1.37 0 2.6-.6 3.45-1.55L13.2 11l1.5 1.5A4.97 4.97 0 0018 14c2.76 0 5-2.24 5-5 0-1.67-.82-3.15-2.07-4.06L20 6v14z"/>',
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
<title>AbleCare – Healthcare Facilities</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/facilities.css">
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
      <h1>Healthcare Facilities</h1>
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
      <div class="greeting-org">Healthcare Facilities — Municipality of Nasugbu, Batangas</div>
      <div class="greeting-badges">
        <div class="gbadge">
          <div class="gbadge-label">Total Facilities</div>
          <div class="gbadge-val">—</div>
        </div>
        <div class="gbadge">
          <div class="gbadge-label">Active</div>
          <div class="gbadge-val">—</div>
        </div>
        <div class="gbadge">
          <div class="gbadge-label">Inactive</div>
          <div class="gbadge-val">—</div>
        </div>
      </div>
    </div>

    <!-- Facilities Table Panel -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          Healthcare Facilities
        </div>
        <div class="panel-actions">
          <button class="btn-add-facility" onclick="openModal('addFacilityModal')">
            <?= icon('plus', $svg) ?>
            Add Facility
          </button>
        </div>
      </div>

      <!-- Placeholder table (no data — ready for DB integration) -->
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Facility Name</th>
              <th>Type</th>
              <th>Barangay</th>
              <th>Contact Number</th>
              <th>In-Charge</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($facilities)): ?>
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <?= icon('empty', $svg) ?>
                  <p>No healthcare facilities have been added yet.</p>
                </div>
              </td>
            </tr>
            <?php else: ?>
              <?php foreach ($facilities as $f): ?>
              <tr>
                <td><strong><?= htmlspecialchars($f['name'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($f['type'] ?? '') ?></td>
                <td><?= htmlspecialchars($f['barangay'] ?? '') ?></td>
                <td><?= htmlspecialchars($f['contact'] ?? '') ?></td>
                <td><?= htmlspecialchars($f['in_charge'] ?? '') ?></td>
                <td><span class="status-badge status-<?= htmlspecialchars($f['status'] ?? 'active') ?>"><?= htmlspecialchars(ucfirst($f['status'] ?? 'active')) ?></span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-view" onclick="openModal('viewFacilityModal')" title="View">
                      <?= icon('eye', $svg) ?> View
                    </button>
                    <button class="btn-edit" onclick="openModal('editFacilityModal')" title="Edit">
                      <?= icon('edit', $svg) ?> Edit
                    </button>
                    <button class="btn-delete" onclick="openModal('deleteFacilityModal')" title="Delete">
                      <?= icon('delete', $svg) ?> Delete
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->


<!-- ══════════════════════════════════════════
     MODAL: ADD FACILITY
══════════════════════════════════════════ -->
<div class="modal-overlay" id="addFacilityModal">
  <div class="modal" style="max-width: 580px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">Add Facility</div>
        <div class="modal-subtitle">Fill in the details to register a new healthcare facility.</div>
      </div>
      <button class="modal-close" onclick="closeModal('addFacilityModal')" aria-label="Close">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
      </button>
    </div>

    <form method="POST" action="facilities.php">
      <input type="hidden" name="add_facility" value="1">
      <div class="modal-body">

        <div class="form-section-label">Facility Information</div>

        <div class="form-group">
          <label class="form-label" for="facility_name">Facility Name</label>
          <input class="form-input" type="text" id="facility_name" name="facility_name" placeholder="e.g., Nasugbu Rural Health Unit" required>
        </div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="facility_type">Facility Type</label>
            <select class="form-select" id="facility_type" name="facility_type" required>
              <option value="" disabled selected>Select type</option>
              <option value="rural_health_unit">Rural Health Unit</option>
              <option value="barangay_health_center">Barangay Health Center</option>
              <option value="barangay_health_station">Barangay Health Station</option>
              <option value="hospital">Hospital</option>
              <option value="clinic">Clinic</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="facility_status">Status</label>
            <select class="form-select" id="facility_status" name="facility_status">
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>

        <div style="margin-top:16px;" class="form-section-label">Location</div>

        <div class="form-group">
          <label class="form-label" for="barangay">Barangay</label>
          <input class="form-input" type="text" id="barangay" name="barangay" placeholder="e.g., Brgy. Poblacion">
        </div>

        <div class="form-group">
          <label class="form-label" for="address">Full Address</label>
          <input class="form-input" type="text" id="address" name="address" placeholder="Street, Barangay, Nasugbu, Batangas">
        </div>

        <div class="form-section-label" style="margin-top:4px;">Contact Details</div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="contact_number">Contact Number</label>
            <input class="form-input" type="text" id="contact_number" name="contact_number" placeholder="0912-345-6789">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="email">Email Address</label>
            <input class="form-input" type="email" id="email" name="email" placeholder="facility@example.com">
          </div>
        </div>

        <div style="margin-top:16px;" class="form-section-label">Person In-Charge</div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="incharge_name">Name</label>
            <input class="form-input" type="text" id="incharge_name" name="incharge_name" placeholder="Full name">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="incharge_position">Position</label>
            <input class="form-input" type="text" id="incharge_position" name="incharge_position" placeholder="e.g., Doctor, Nurse, Midwife">
          </div>
        </div>

        <div style="margin-top:16px;" class="form-group">
          <label class="form-label" for="notes">Notes / Remarks</label>
          <textarea class="form-textarea" id="notes" name="notes" placeholder="Optional notes about this facility..."></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('addFacilityModal')">Cancel</button>
        <button type="submit" class="btn-primary">Add Facility</button>
      </div>
    </form>
  </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL: VIEW FACILITY
══════════════════════════════════════════ -->
<div class="modal-overlay" id="viewFacilityModal">
  <div class="modal" style="max-width: 520px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">Facility Details</div>
        <div class="modal-subtitle">Viewing facility information.</div>
      </div>
      <button class="modal-close" onclick="closeModal('viewFacilityModal')" aria-label="Close">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <!-- Populated via JS when DB is connected -->
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div><div class="form-label" style="margin-bottom:4px;">Facility Name</div><div style="font-size:14px;color:var(--text-dark);font-weight:600;" id="view_name">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Type</div><div style="font-size:14px;color:var(--text-dark);" id="view_type">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Barangay</div><div style="font-size:14px;color:var(--text-dark);" id="view_barangay">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Full Address</div><div style="font-size:14px;color:var(--text-dark);" id="view_address">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Contact Number</div><div style="font-size:14px;color:var(--text-dark);" id="view_contact">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Email</div><div style="font-size:14px;color:var(--text-dark);" id="view_email">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Person In-Charge</div><div style="font-size:14px;color:var(--text-dark);" id="view_incharge">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Status</div><div id="view_status">—</div></div>
        <div><div class="form-label" style="margin-bottom:4px;">Notes</div><div style="font-size:14px;color:var(--text-muted);" id="view_notes">—</div></div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-cancel" onclick="closeModal('viewFacilityModal')">Close</button>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL: EDIT FACILITY
══════════════════════════════════════════ -->
<div class="modal-overlay" id="editFacilityModal">
  <div class="modal" style="max-width: 580px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">Edit Facility</div>
        <div class="modal-subtitle">Update the facility's information.</div>
      </div>
      <button class="modal-close" onclick="closeModal('editFacilityModal')" aria-label="Close">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
      </button>
    </div>

    <form method="POST" action="facilities.php">
      <input type="hidden" name="edit_facility" value="1">
      <input type="hidden" name="facility_id" id="edit_facility_id" value="">
      <div class="modal-body">

        <div class="form-section-label">Facility Information</div>

        <div class="form-group">
          <label class="form-label" for="edit_facility_name">Facility Name</label>
          <input class="form-input" type="text" id="edit_facility_name" name="facility_name" placeholder="e.g., Nasugbu Rural Health Unit" required>
        </div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="edit_facility_type">Facility Type</label>
            <select class="form-select" id="edit_facility_type" name="facility_type" required>
              <option value="" disabled>Select type</option>
              <option value="rural_health_unit">Rural Health Unit</option>
              <option value="barangay_health_center">Barangay Health Center</option>
              <option value="barangay_health_station">Barangay Health Station</option>
              <option value="hospital">Hospital</option>
              <option value="clinic">Clinic</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="edit_facility_status">Status</label>
            <select class="form-select" id="edit_facility_status" name="facility_status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>

        <div style="margin-top:16px;" class="form-section-label">Location</div>

        <div class="form-group">
          <label class="form-label" for="edit_barangay">Barangay</label>
          <input class="form-input" type="text" id="edit_barangay" name="barangay" placeholder="e.g., Brgy. Poblacion">
        </div>

        <div class="form-group">
          <label class="form-label" for="edit_address">Full Address</label>
          <input class="form-input" type="text" id="edit_address" name="address" placeholder="Street, Barangay, Nasugbu, Batangas">
        </div>

        <div class="form-section-label" style="margin-top:4px;">Contact Details</div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="edit_contact_number">Contact Number</label>
            <input class="form-input" type="text" id="edit_contact_number" name="contact_number" placeholder="0912-345-6789">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="edit_email">Email Address</label>
            <input class="form-input" type="email" id="edit_email" name="email" placeholder="facility@example.com">
          </div>
        </div>

        <div style="margin-top:16px;" class="form-section-label">Person In-Charge</div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="edit_incharge_name">Name</label>
            <input class="form-input" type="text" id="edit_incharge_name" name="incharge_name" placeholder="Full name">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="edit_incharge_position">Position</label>
            <input class="form-input" type="text" id="edit_incharge_position" name="incharge_position" placeholder="e.g., Doctor, Nurse, Midwife">
          </div>
        </div>

        <div style="margin-top:16px;" class="form-group">
          <label class="form-label" for="edit_notes">Notes / Remarks</label>
          <textarea class="form-textarea" id="edit_notes" name="notes" placeholder="Optional notes..."></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal('editFacilityModal')">Cancel</button>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL: DELETE CONFIRM
══════════════════════════════════════════ -->
<div class="modal-overlay" id="deleteFacilityModal">
  <div class="modal" style="max-width: 420px;">
    <div class="modal-body" style="padding-top:32px;">
      <div class="delete-icon-wrap">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
      </div>
      <div class="delete-modal-text">
        <h3>Delete Facility</h3>
        <p>Are you sure you want to delete this facility? This action cannot be undone.</p>
      </div>
    </div>
    <div class="modal-footer" style="justify-content:center; gap:12px;">
      <button type="button" class="btn-cancel" onclick="closeModal('deleteFacilityModal')">Cancel</button>
      <form method="POST" action="facilities.php" style="display:inline;">
        <input type="hidden" name="delete_facility" value="1">
        <input type="hidden" name="facility_id" id="delete_facility_id" value="">
        <button type="submit" class="btn-danger">Yes, Delete</button>
      </form>
    </div>
  </div>
</div>



<script>
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
  function exportFacilities(format) {
    closeModal('exportModal');
    window.location.href = 'export_facilities.php?format=' + format;
  }

  /*
   * ── HOOK THESE UP WHEN DB IS READY ────
   *
   * For the View button, pass facility data:
   *   document.getElementById('view_name').textContent = row.facility_name;
   *   document.getElementById('view_type').textContent = row.facility_type;
   *   ... etc.
   *
   * For the Edit button, pre-populate the edit form:
   *   document.getElementById('edit_facility_id').value = row.id;
   *   document.getElementById('edit_facility_name').value = row.facility_name;
   *   ... etc.
   *
   * For the Delete button, set the hidden ID:
   *   document.getElementById('delete_facility_id').value = row.id;
   */
</script>

</body>
</html>