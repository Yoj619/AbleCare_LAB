<?php
// ============================================================
//  ABLECARE – Emergency Alert Monitor
//  Municipality of Nasugbu, Batangas
// ============================================================

session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'lgu_admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// ── AJAX: update alert status (Responded / Resolved) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $input   = json_decode(file_get_contents('php://input'), true) ?? [];
    $alertId = (int) ($input['alert_id'] ?? 0);
    $action  = $input['action']  ?? '';

    if ($alertId <= 0 || !in_array($action, ['responded', 'resolved'], true)) {
        echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
        exit;
    }

    $db = get_db();

    if ($action === 'resolved') {
        $stmt = $db->prepare(
            'UPDATE emergency_alerts
             SET status = "resolved", resolved_at = NOW()
             WHERE id = ? LIMIT 1'
        );
    } else {
        $stmt = $db->prepare(
            'UPDATE emergency_alerts
             SET status = "responded"
             WHERE id = ? AND status = "active" LIMIT 1'
        );
    }
    $stmt->bind_param('i', $alertId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true]);
    exit;
}

// ── DB queries ─────────────────────────────────────────────────────────────
$db = get_db();

$total_today    = (int) $db->query(
    "SELECT COUNT(*) FROM emergency_alerts WHERE DATE(created_at) = CURDATE()"
)->fetch_row()[0];

$resolved_today = (int) $db->query(
    "SELECT COUNT(*) FROM emergency_alerts WHERE DATE(created_at) = CURDATE() AND status = 'resolved'"
)->fetch_row()[0];

$stmt = $db->prepare(
    "SELECT ea.id, ea.status, ea.created_at, ea.latitude, ea.longitude,
            CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
            CONCAT(u.first_name, ' ', u.last_name) AS caregiver_name,
            u.phone_number
     FROM emergency_alerts ea
     JOIN patients   p ON p.id = ea.patient_id
     JOIN caregivers c ON c.id = ea.caregiver_id
     JOIN users      u ON u.id = c.user_id
     WHERE ea.status IN ('active', 'responded')
     ORDER BY ea.created_at DESC"
);
$stmt->execute();
$alerts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Page setup ─────────────────────────────────────────────────────────────
$admin = [
    'name'   => $_SESSION['full_name'] ?? 'Administrator',
    'role'   => $_SESSION['role']      ?? 'lgu_admin',
    'avatar' => $_SESSION['avatar']    ?? '',
];

$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => false],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => false],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => false],
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => true],
    ['icon' => 'notifications',   'label' => 'System Notifications',   'href' => 'notifications.php',     'active' => false],
    ['icon' => 'reports',         'label' => 'Reports & Activity Log', 'href' => 'reports.php',           'active' => false],
    ['icon' => 'settings',        'label' => 'Account Settings',       'href' => 'settings.php',          'active' => false],
];

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
    'download'        => '<path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>',
    'refresh'         => '<path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>',
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AbleCare – Emergency Monitor</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/emergency_monitor.css">
<style>
/* ── Alert card grid ──────────────────────────────────────────────────── */
.alerts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 18px;
}

.alert-card {
  background: #fff;
  border-radius: 14px;
  border: 1.5px solid #dde8e7;
  box-shadow: 0 2px 14px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  transition: box-shadow .15s, border-color .15s;
}
.alert-card:hover           { box-shadow: 0 6px 28px rgba(0,0,0,0.10); }
.alert-card[data-status="responded"] { border-color: #f0a500; }

/* card header */
.alert-card-head {
  padding: 11px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eef3f3;
  background: #f8fbfb;
  border-radius: 12px 12px 0 0;
  gap: 8px;
}

.status-pill {
  padding: 3px 11px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .6px;
  white-space: nowrap;
}
.pill-active    { background: #fde8e2; color: #b84030; }
.pill-responded { background: #fff3d6; color: #92400e; }

.elapsed-timer {
  font-size: 11.5px;
  color: #9db8b8;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

/* card body */
.alert-card-body {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.alert-names { display: flex; flex-direction: column; gap: 3px; }

.alert-patient {
  font-size: 15px;
  font-weight: 700;
  color: #1c3030;
}
.alert-caregiver {
  font-size: 13px;
  color: #5a7070;
}
.alert-caregiver span {
  color: #9db8b8;
  margin: 0 4px;
}

.alert-meta { display: flex; flex-direction: column; gap: 7px; }

.alert-meta-row {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  font-size: 12.5px;
  color: #5a7070;
  line-height: 1.45;
}
.meta-icon { flex-shrink: 0; font-size: 13px; margin-top: 1px; width: 16px; text-align: center; }
.meta-val  { flex: 1; }
.meta-val strong { color: #1c3030; font-weight: 600; }
.meta-coords { font-family: 'DM Mono', 'Courier New', monospace; font-size: 11.5px; color: #3aafa9; }

/* card footer */
.alert-card-foot {
  padding: 11px 16px;
  border-top: 1px solid #eef3f3;
  background: #f8fbfb;
  border-radius: 0 0 12px 12px;
  display: flex;
  gap: 8px;
}

.btn-card {
  flex: 1;
  padding: 7px 10px;
  border-radius: 7px;
  font-size: 12.5px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  border: 1.5px solid transparent;
  transition: opacity .15s;
}
.btn-card:disabled { opacity: .4; cursor: not-allowed; }

.btn-responded-card {
  background: #fff3d6;
  color: #92400e;
  border-color: #f0a500;
}
.btn-responded-card:not(:disabled):hover { opacity: .8; }

.btn-resolved-card {
  background: #3aafa9;
  color: #fff;
  border-color: #3aafa9;
}
.btn-resolved-card:not(:disabled):hover { opacity: .87; }

/* empty state */
.alerts-empty {
  text-align: center;
  padding: 52px 24px;
  color: #9db8b8;
}
.alerts-empty svg {
  width: 48px; height: 48px;
  fill: #c8e0de;
  margin-bottom: 16px;
}
.alerts-empty p { font-size: 15px; margin: 0; }

/* live stats */
.stat-card-active {
  border-color: #fde8e2 !important;
  background: linear-gradient(135deg, #fff8f7, #fff) !important;
}
</style>
</head>
<body>

<!-- ══════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════ -->
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
      <a href="<?= htmlspecialchars($item['href']) ?>"
         class="nav-item<?= $item['active'] ? ' active' : '' ?>">
        <span class="nav-icon"><?= icon($item['icon'], $svg) ?></span>
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
          <div class="admin-chip-role"><?= htmlspecialchars(formatRole($admin['role'])) ?></div>
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
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-label">Total Alerts Today</div>
          <div class="stat-icon teal">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <polyline points="3 12 6 6 9 14 12 10 15 16 18 8 21 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:var(--teal);"/>
            </svg>
          </div>
        </div>
        <?php if ($total_today > 0): ?>
          <div class="stat-value"><?= $total_today ?></div>
        <?php else: ?>
          <div class="stat-value" style="font-size:28px;color:var(--text-muted);">—</div>
          <div class="stat-delta">No alerts yet today</div>
        <?php endif; ?>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-label">Resolved Today</div>
          <div class="stat-icon green">
            <?= icon('shield', $svg) ?>
          </div>
        </div>
        <?php if ($resolved_today > 0): ?>
          <div class="stat-value"><?= $resolved_today ?></div>
          <?php $rate = $total_today > 0 ? round(($resolved_today / $total_today) * 100) : 0; ?>
          <div class="stat-sub"><?= $rate ?>% resolution rate</div>
        <?php else: ?>
          <div class="stat-value" style="font-size:28px;color:var(--text-muted);">—</div>
          <div class="stat-delta">No resolved alerts</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Alert cards panel -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title" style="display:flex;align-items:center;gap:10px;">
          <?php if (!empty($alerts)): ?>
            <span class="pulse-dot" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e05f3e;animation:pulse 1.4s infinite;"></span>
            <?= count($alerts) ?> Active Alert<?= count($alerts) !== 1 ? 's' : '' ?>
          <?php else: ?>
            Live Feed
          <?php endif; ?>
        </div>
        <span style="font-size:12px;color:var(--text-muted);font-weight:500;" id="lastRefreshed">
          Loaded <?= date('g:i:s A') ?>
        </span>
      </div>

      <?php if (empty($alerts)): ?>
        <div class="alerts-empty">
          <?= icon('shield', $svg) ?>
          <p>No active emergency alerts at this time.</p>
        </div>
      <?php else: ?>
        <div class="alerts-grid" id="alertsGrid">
          <?php foreach ($alerts as $alert):
            $ts          = strtotime($alert['created_at']);
            $statusClass = $alert['status'] === 'responded' ? 'pill-responded' : 'pill-active';
            $statusLabel = ucfirst($alert['status']);
            $triggeredFmt = date('M j, Y · g:i:s A', $ts);
            $lat = $alert['latitude']  !== null ? number_format((float)$alert['latitude'],  7) : '—';
            $lng = $alert['longitude'] !== null ? number_format((float)$alert['longitude'], 7) : '—';
          ?>
          <div class="alert-card" id="alert-<?= (int)$alert['id'] ?>" data-status="<?= htmlspecialchars($alert['status']) ?>">

            <!-- Header: status pill + live elapsed time -->
            <div class="alert-card-head">
              <span class="status-pill <?= $statusClass ?>" id="pill-<?= (int)$alert['id'] ?>">
                <?= htmlspecialchars($statusLabel) ?>
              </span>
              <span class="elapsed-timer" data-ts="<?= $ts ?>">—</span>
            </div>

            <!-- Body -->
            <div class="alert-card-body">
              <div class="alert-names">
                <div class="alert-patient"><?= htmlspecialchars($alert['patient_name']) ?></div>
                <div class="alert-caregiver">
                  Caregiver<span>·</span><?= htmlspecialchars($alert['caregiver_name']) ?>
                  <?php if (!empty($alert['phone_number'])): ?>
                    <span>·</span><?= htmlspecialchars($alert['phone_number']) ?>
                  <?php endif; ?>
                </div>
              </div>

              <div class="alert-meta">
                <div class="alert-meta-row">
                  <span class="meta-icon">📍</span>
                  <span class="meta-val">
                    <strong>Live GPS</strong><br>
                    <span class="meta-coords">
                      Lat: <?= $lat ?>,&nbsp; Lng: <?= $lng ?>
                    </span>
                  </span>
                </div>
                <div class="alert-meta-row">
                  <span class="meta-icon">🕐</span>
                  <span class="meta-val">
                    <strong>Triggered</strong> <?= htmlspecialchars($triggeredFmt) ?>
                  </span>
                </div>
                <div class="alert-meta-row">
                  <span class="meta-icon">⏱</span>
                  <span class="meta-val elapsed-timer" data-ts="<?= $ts ?>">—</span>
                </div>
              </div>
            </div>

            <!-- Footer: action buttons -->
            <div class="alert-card-foot">
              <?php if ($alert['status'] === 'active'): ?>
              <button class="btn-card btn-responded-card"
                      onclick="updateStatus(<?= (int)$alert['id'] ?>, 'responded', this)">
                Responded
              </button>
              <?php endif; ?>
              <button class="btn-card btn-resolved-card"
                      onclick="updateStatus(<?= (int)$alert['id'] ?>, 'resolved', this)">
                Resolved
              </button>
            </div>

          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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
        <div class="modal-subtitle">Select the format and date range for the emergency alerts report:</div>
      </div>
      <button class="modal-close" onclick="closeModal('downloadReportModal')" title="Close">
        <?= icon('close', $svg) ?>
      </button>
    </div>
    <div class="modal-body">
      <div>
        <label class="form-label">Date Range</label>
        <div class="date-range-row">
          <input type="date" class="form-input" id="report_date_from">
          <input type="date" class="form-input" id="report_date_to">
        </div>
      </div>
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


<script>
// ── Elapsed time counter ────────────────────────────────────────────────────

function formatElapsed(seconds) {
  if (seconds < 60)    return seconds + 's elapsed';
  if (seconds < 3600) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return m + 'm ' + s + 's elapsed';
  }
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  return h + 'h ' + m + 'm elapsed';
}

function tickElapsed() {
  const now = Math.floor(Date.now() / 1000);
  document.querySelectorAll('.elapsed-timer[data-ts]').forEach(function(el) {
    const ts = parseInt(el.dataset.ts, 10);
    if (!isNaN(ts)) el.textContent = formatElapsed(Math.max(0, now - ts));
  });
}

tickElapsed();
setInterval(tickElapsed, 1000);


// ── AJAX: update alert status ───────────────────────────────────────────────

async function updateStatus(alertId, action, btn) {
  const card = document.getElementById('alert-' + alertId);
  if (!card) return;

  // Disable all buttons on this card while the request is in flight
  card.querySelectorAll('.btn-card').forEach(function(b) { b.disabled = true; });

  try {
    const res  = await fetch(window.location.pathname, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ alert_id: alertId, action: action }),
    });
    const data = await res.json();

    if (!data.ok) throw new Error(data.error || 'Update failed.');

    if (action === 'resolved') {
      // Animate card out, then remove it from the live feed
      card.style.transition = 'opacity .35s, transform .35s';
      card.style.opacity    = '0';
      card.style.transform  = 'scale(0.96)';
      setTimeout(function() { card.remove(); }, 380);
    } else {
      // Flip status pill to "Responded" and hide the Responded button
      const pill = document.getElementById('pill-' + alertId);
      if (pill) {
        pill.textContent  = 'Responded';
        pill.className    = 'status-pill pill-responded';
      }
      card.dataset.status = 'responded';
      const respondedBtn  = card.querySelector('.btn-responded-card');
      if (respondedBtn) respondedBtn.remove();

      // Re-enable remaining buttons
      card.querySelectorAll('.btn-card').forEach(function(b) { b.disabled = false; });
    }
  } catch (e) {
    alert('Could not update alert: ' + e.message);
    // Re-enable all buttons on failure
    card.querySelectorAll('.btn-card').forEach(function(b) { b.disabled = false; });
  }
}


// ── Modal helpers ───────────────────────────────────────────────────────────

function openModal(id) {
  var el = document.getElementById(id);
  if (el) { el.classList.add('active'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
  var el = document.getElementById(id);
  if (el) { el.classList.remove('active'); document.body.style.overflow = ''; }
}

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape')
    document.querySelectorAll('.modal-overlay.active').forEach(function(o) { closeModal(o.id); });
});

function exportReport(format) {
  var from = document.getElementById('report_date_from').value;
  var to   = document.getElementById('report_date_to').value;
  closeModal('downloadReportModal');
  window.location.href = 'export_emergency_report.php?format=' + format + '&from=' + from + '&to=' + to;
}
</script>

</body>
</html>
