<?php
// ============================================================
//  ABLECARE – Emergency Alert Monitor
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
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => true],
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
    'logout'          => '<path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>',
    'close'           => '<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>',
    'eye'             => '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>',
    'download'        => '<path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>',
    'refresh'         => '<path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>',
    'filter'          => '<path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/>',
    'pulse'           => '<path d="M3.5 18.5l6-6 4 4L22 6.92"/>',
    'shield'          => '<path d="M12 2L3 7v6c0 5.25 3.75 10.15 9 11.35C17.25 23.15 21 18.25 21 13V7L12 2zm-1 14l-3-3 1.41-1.41L11 13.17l4.59-4.58L17 10l-6 6z"/>',
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

// ── Mock alert data (replace with PDO queries when DB is ready) ──
/*
$pdo = new PDO('mysql:host=localhost;dbname=ablecare', $db_user, $db_pass);

$stmt = $pdo->prepare("
    SELECT a.id, p.full_name AS patient_name, c.full_name AS caregiver_name,
           a.created_at, a.barangay, a.status
    FROM emergency_alerts a
    JOIN patients p ON a.patient_id = p.id
    JOIN caregivers c ON a.caregiver_id = c.id
    ORDER BY a.created_at DESC
");
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_today = (int) $pdo->query("
    SELECT COUNT(*) FROM emergency_alerts WHERE DATE(created_at) = CURDATE()
")->fetchColumn();

$resolved_today = (int) $pdo->query("
    SELECT COUNT(*) FROM emergency_alerts WHERE DATE(created_at) = CURDATE() AND status = 'resolved'
")->fetchColumn();
*/

$alerts = [];  // empty until DB is ready
$total_today    = 0;
$resolved_today = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AbleCare – Emergency Monitor</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/emergency_monitor.css">

</head>
<body>

<!-- ══════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════ -->
<aside class="sidebar">
  <div class="sidebar-brand">
     <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:50px;height:auto;">
    <div>
      <div class="brand-name">AbleCare</div>
      <div class="brand-sub">LGU Admin Portal</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
      <a href="<?= htmlspecialchars($item['href']) ?>"
         class="nav-item<?= $item['active'] ? ' active' : '' ?>">
        <span class="nav-icon">
          <?= icon($item['icon'], $svg) ?>
        </span>
        <?= htmlspecialchars($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <button class="logout-btn" onclick="window.location.href='logout.php'">
      <?= icon('logout', $svg) ?>
      Logout
    </button>
  </div>
</aside>


<!-- ══════════════════════════════════════════
     MAIN
══════════════════════════════════════════ -->
<div class="main">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-title">
      <h1>LGU Health Office Dashboard</h1>
      <p>Municipality of Nasugbu, Batangas</p>
    </div>
    <div class="topbar-right">
      <button class="notif-btn" onclick="window.location.href='notifications.php'" title="Notifications">
        <?= icon('bell', $svg) ?>
      </button>
      <div class="admin-chip">
        <div class="admin-avatar">
          <?php if (!empty($admin['avatar'])): ?>
            <img src="<?= htmlspecialchars($admin['avatar']) ?>" alt="avatar">
          <?php else: ?>
            <?= strtoupper(substr($admin['name'], 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="admin-chip-name"><?= htmlspecialchars($admin['name']) ?></div>
          <div class="admin-chip-role"><?= formatRole($admin['role']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="content">

    <!-- Page header -->
    <div class="page-header">
      <div class="page-header-left">
        <h2>
          Emergency Alert Monitor
          <span class="live-badge">
            <span class="live-dot"></span>
            Live
          </span>
        </h2>
        <div class="breadcrumb">
          <a href="dashboard_admin.php">Dashboard</a>
          &rsaquo;
          <span>Emergency Monitor</span>
        </div>
      </div>
      <div class="page-header-actions">
        <button class="btn-outline" onclick="openModal('downloadReportModal')">
          <?= icon('download', $svg) ?>
          Download Report
        </button>
        <button class="btn-primary" onclick="location.reload()">
          <?= icon('refresh', $svg) ?>
          Refresh
        </button>
      </div>
    </div>

    <!-- Stat cards -->
    <div class="stats-row">
      <!-- Total Alerts Today -->
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-label">Total Alerts Today</div>
          <div class="stat-icon teal">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <!-- pulse/activity icon -->
              <polyline points="3 12 6 6 9 14 12 10 15 16 18 8 21 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:var(--teal);"/>
            </svg>
          </div>
        </div>
        <?php if ($total_today > 0): ?>
          <div class="stat-value"><?= $total_today ?></div>
          <div class="stat-delta down">↘ compared to yesterday</div>
        <?php else: ?>
          <div class="stat-value" style="font-size:28px; color:var(--text-muted);">—</div>
          <div class="stat-delta">No data yet</div>
        <?php endif; ?>
      </div>

      <!-- Resolved Today -->
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-label">Resolved Today</div>
          <div class="stat-icon green">
            <?= icon('shield', $svg) ?>
          </div>
        </div>
        <?php if ($resolved_today > 0): ?>
          <div class="stat-value"><?= $resolved_today ?></div>
          <?php $rate = $total_today > 0 ? round(($resolved_today / $total_today) * 100, 1) : 0; ?>
          <div class="stat-sub"><?= $rate ?>% resolution rate</div>
        <?php else: ?>
          <div class="stat-value" style="font-size:28px; color:var(--text-muted);">—</div>
          <div class="stat-delta">No data yet</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Alerts table panel -->
    <div class="panel">
      <div class="panel-header">
        <div class="filter-group">
          <select class="filter-select" id="filterStatus">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="resolved">Resolved</option>
            <option value="pending">Pending</option>
          </select>
          <div class="filter-divider">
            <?= icon('filter', $svg) ?>
          </div>
          <input type="date" class="filter-date" id="filterDate" value="<?= date('Y-m-d') ?>">
          <select class="filter-location filter-select" id="filterLocation">
            <option value="">Select Location</option>
            <option value="brgy_san_jose">Brgy. San Jose</option>
            <option value="brgy_poblacion">Brgy. Poblacion</option>
            <option value="brgy_santa_cruz">Brgy. Santa Cruz</option>
          </select>
        </div>
      </div>

      <table id="alertsTable">
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Caregiver</th>
            <th>Date &amp; Time</th>
            <th>Location</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($alerts)): ?>
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <?= icon('emergency', $svg) ?>
                  <p>No emergency alerts recorded yet.</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($alerts as $alert): ?>
              <tr>
                <td class="patient-name"><?= htmlspecialchars($alert['patient_name']) ?></td>
                <td class="caregiver-name"><?= htmlspecialchars($alert['caregiver_name']) ?></td>
                <td><?= date('M j, g:i A', strtotime($alert['created_at'])) ?></td>
                <td class="location-name"><?= htmlspecialchars($alert['barangay']) ?></td>
                <td>
                  <button class="btn-view-icon"
                          onclick="openViewModal(<?= htmlspecialchars(json_encode($alert)) ?>)"
                          title="View details">
                    <?= icon('eye', $svg) ?>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div><!-- /panel -->

  </div><!-- /content -->
</div><!-- /main -->


<!-- ══════════════════════════════════════════
     MODAL: DOWNLOAD REPORT
══════════════════════════════════════════ -->
<div class="modal-overlay" id="downloadReportModal">
  <div class="modal">

    <div class="modal-header">
      <div>
        <div class="modal-title">Download Emergency Report</div>
        <div class="modal-subtitle">Select the format and date range for the<br>emergency alerts report:</div>
      </div>
      <button class="modal-close" onclick="closeModal('downloadReportModal')" title="Close">
        <?= icon('close', $svg) ?>
      </button>
    </div>

    <div class="modal-body">

      <!-- Date Range -->
      <div>
        <label class="form-label">Date Range</label>
        <div class="date-range-row">
          <input type="date" class="form-input" id="report_date_from" placeholder="From">
          <input type="date" class="form-input" id="report_date_to"   placeholder="To">
        </div>
      </div>

      <!-- Export Format -->
      <div>
        <div class="export-format-label">Export Format</div>
        <div class="export-format-list">
          <button class="export-format-btn" onclick="exportReport('pdf')">Download as PDF</button>
          <button class="export-format-btn" onclick="exportReport('xlsx')">Download as Excel (XLSX)</button>
          <button class="export-format-btn" onclick="exportReport('csv')">Download as CSV</button>
        </div>
      </div>

    </div>

    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal('downloadReportModal')">Cancel</button>
    </div>

  </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL: VIEW ALERT DETAILS
══════════════════════════════════════════ -->
<div class="modal-overlay" id="viewAlertModal">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Alert Details</div>
        <div class="modal-subtitle">Emergency alert information</div>
      </div>
      <button class="modal-close" onclick="closeModal('viewAlertModal')" title="Close">
        <?= icon('close', $svg) ?>
      </button>
    </div>
    <div class="modal-body">
      <div class="alert-detail-grid">
        <div class="alert-detail-item">
          <label>Patient Name</label>
          <span id="view_patient_name">—</span>
        </div>
        <div class="alert-detail-item">
          <label>Caregiver</label>
          <span id="view_caregiver_name">—</span>
        </div>
        <div class="alert-detail-item">
          <label>Date &amp; Time</label>
          <span id="view_datetime">—</span>
        </div>
        <div class="alert-detail-item">
          <label>Location</label>
          <span id="view_location">—</span>
        </div>
        <div class="alert-detail-item">
          <label>Status</label>
          <span id="view_status">—</span>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal('viewAlertModal')">Close</button>
    </div>
  </div>
</div>


<script>
  // ── Modal helpers ──────────────────────
  function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('active'); document.body.style.overflow = 'hidden'; }
  }

  function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('active'); document.body.style.overflow = ''; }
  }

  // Close on backdrop click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) closeModal(this.id);
    });
  });

  // Close on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape')
      document.querySelectorAll('.modal-overlay.active').forEach(o => closeModal(o.id));
  });

  // ── Export report ──────────────────────
  function exportReport(format) {
    const from = document.getElementById('report_date_from').value;
    const to   = document.getElementById('report_date_to').value;
    closeModal('downloadReportModal');
    window.location.href = `export_emergency_report.php?format=${format}&from=${from}&to=${to}`;
  }

  // ── View alert details ─────────────────
  function openViewModal(alert) {
    document.getElementById('view_patient_name').textContent  = alert.patient_name  ?? '—';
    document.getElementById('view_caregiver_name').textContent = alert.caregiver_name ?? '—';
    document.getElementById('view_datetime').textContent      = alert.created_at    ?? '—';
    document.getElementById('view_location').textContent      = alert.barangay      ?? '—';
    document.getElementById('view_status').textContent        = alert.status        ?? '—';
    openModal('viewAlertModal');
  }

  /*
   * ── HOOK THESE UP WHEN DB IS READY ────
   *
   * Filter by status / date / location:
   *   document.getElementById('filterStatus').addEventListener('change', applyFilters);
   *   document.getElementById('filterDate').addEventListener('change', applyFilters);
   *   document.getElementById('filterLocation').addEventListener('change', applyFilters);
   *
   * function applyFilters() {
   *   const status   = document.getElementById('filterStatus').value;
   *   const date     = document.getElementById('filterDate').value;
   *   const location = document.getElementById('filterLocation').value;
   *   window.location.href = `emergency_monitor.php?status=${status}&date=${date}&location=${location}`;
   * }
   */
</script>

</body>
</html>
