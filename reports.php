<?php
// ============================================================
//  ABLECARE – Reports & Activity Log
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

// ── Active tab ──
$active_tab = $_GET['tab'] ?? 'summary';

// ── Nav items ──
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => false],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => false],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => false],
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => false],
    ['icon' => 'notifications',   'label' => 'System Notifications',   'href' => 'notifications.php',     'active' => false],
    ['icon' => 'reports',         'label' => 'Reports & Activity Log', 'href' => 'reports.php',           'active' => true],
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
    'download'        => '<path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>',
    'generate'        => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zm-2 8H7v-2h4v2zm6-4H7v-2h10v2z"/>',
    'search'          => '<path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>',
    'trend-up'        => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    'bar-chart'       => '<rect x="18" y="3" width="4" height="18"/><rect x="10" y="8" width="4" height="13"/><rect x="2" y="13" width="4" height="8"/>',
    'line-chart'      => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    'log-icon'        => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/>',
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

// ── Static front-end data ──────────────────────────────────
// In the future, replace these arrays with PDO queries.

// Summary stat cards
$summary_stats = [
    ['label' => 'Total Emergency Alerts', 'value' => '131', 'period' => 'Jan – Mar 2026', 'color' => '#e05a2b'],
    ['label' => 'New Caregivers',         'value' => '105', 'period' => 'Jan – Mar 2026', 'color' => '#e05a2b'],
    ['label' => 'New Providers',          'value' => '19',  'period' => 'Jan – Mar 2026', 'color' => '#e05a2b'],
];

// Therapy records table rows
$therapy_records = [
    ['patient' => 'Juan dela Cruz',  'type' => 'Physical Therapy',    'sessions' => '8/12',  'status' => 'In Progress', 'provider' => 'Dr. Jose Rizal',    'date' => 'Mar 31, 2026'],
    ['patient' => 'Rosa Martinez',   'type' => 'Occupational Therapy','sessions' => '12/12', 'status' => 'Completed',   'provider' => 'Dr. Miguel Torres',  'date' => 'Mar 28, 2026'],
    ['patient' => 'Carlos Reyes',    'type' => 'Physical Therapy',    'sessions' => '5/10',  'status' => 'In Progress', 'provider' => 'Dr. Jose Rizal',    'date' => 'Mar 30, 2026'],
];

// Activity log entries
$activity_log = [
    ['action' => 'Admin approved caregiver account of Linda Garcia',          'actor' => 'Admin Rodriguez', 'time' => '2 hours ago',  'highlight' => false],
    ['action' => 'Admin updated facility details for Nasugbu General Hospital','actor' => 'Admin Rodriguez', 'time' => '5 hours ago',  'highlight' => false],
    ['action' => 'Admin sent system notification to all caregivers',           'actor' => 'Admin Rodriguez', 'time' => 'Yesterday',    'highlight' => false],
    ['action' => 'Admin generated Emergency Alerts Summary report',            'actor' => 'Admin Rodriguez', 'time' => 'Yesterday',    'highlight' => false],
    ['action' => 'Admin deactivated user account for Carmen Villanueva',       'actor' => 'Admin Rodriguez', 'time' => '2 days ago',   'highlight' => true],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AbleCare – Reports &amp; Activity Log</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="css/reports.css">
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
    <div>
      <div class="page-title">
        <?= icon('reports', $svg) ?>
        Reports &amp; Activity Log
      </div>
      <div class="breadcrumb">
        <a href="dashboard_admin.php">Dashboard</a>
        &rsaquo;
        <span>Reports &amp; Activity Log</span>
      </div>
    </div>

    <!-- Main panel with tabs -->
    <div class="panel">

      <!-- Tab buttons -->
      <div class="tabs-bar">
        <button class="tab-btn<?= $active_tab === 'summary'  ? ' active' : '' ?>" id="tab-summary"  onclick="switchTab('summary')">Summary Reports</button>
        <button class="tab-btn<?= $active_tab === 'therapy'  ? ' active' : '' ?>" id="tab-therapy"  onclick="switchTab('therapy')">Therapy Records Report</button>
        <button class="tab-btn<?= $active_tab === 'activity' ? ' active' : '' ?>" id="tab-activity" onclick="switchTab('activity')">Activity Log</button>
      </div>


      <!-- ══════════════════════════════════
           TAB 1 – SUMMARY REPORTS
      ══════════════════════════════════ -->
      <div class="tab-panel<?= $active_tab === 'summary' ? ' active' : '' ?>" id="panel-summary">

        <!-- Filters - without the icon images -->
        <div class="filter-card">
          <div class="filter-field">
            <label class="filter-label">Start Date</label>
            <input type="date" class="filter-input" id="sum_start" value="2026-01-01">
          </div>
          <div class="filter-field">
            <label class="filter-label">End Date</label>
            <input type="date" class="filter-input" id="sum_end" value="2026-03-31">
          </div>
          <div class="filter-field">
            <label class="filter-label">Report Type</label>
            <select class="filter-select" id="sum_type">
              <option selected>Emergency Alert Summary</option>
              <option>Caregiver Registration Summary</option>
              <option>Healthcare Provider Summary</option>
              <option>Overall Activity Summary</option>
            </select>
          </div>
        </div>

        <!-- Generate button -->
        <button class="btn-generate" onclick="generateReport()">
          <?= icon('generate', $svg) ?>
          Generate Report
        </button>

        <!-- Stat cards -->
        <div class="stats-row">
          <?php foreach ($summary_stats as $s): ?>
            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-card-label"><?= htmlspecialchars($s['label']) ?></div>
                <span class="stat-trend">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" fill="none" stroke="<?= $s['color'] ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
              </div>
              <div class="stat-value"><?= htmlspecialchars($s['value']) ?></div>
              <div class="stat-period"><?= htmlspecialchars($s['period']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Charts -->
        <div class="charts-row">
          <div class="chart-card">
            <div class="chart-title">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <rect x="18" y="3" width="4" height="18" fill="var(--teal)" rx="1"/>
                <rect x="10" y="8" width="4" height="13" fill="var(--teal)" rx="1"/>
                <rect x="2"  y="13" width="4" height="8" fill="var(--teal)" rx="1"/>
              </svg>
              Emergency Alerts Trend
            </div>
            <div class="chart-wrap">
              <canvas id="chartAlerts"></canvas>
            </div>
          </div>
          <div class="chart-card">
            <div class="chart-title">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" fill="none" stroke="var(--teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              User Registration Trend
            </div>
            <div class="chart-wrap">
              <canvas id="chartUsers"></canvas>
            </div>
          </div>
        </div>

        <!-- Download buttons -->
        <div class="download-row">
          <button class="btn-dl-pdf" onclick="downloadReport('pdf')">
            <?= icon('download', $svg) ?>
            Download as PDF
          </button>
          <button class="btn-dl-csv" onclick="downloadReport('csv')">
            <?= icon('download', $svg) ?>
            Download as CSV
          </button>
        </div>

      </div><!-- /panel-summary -->


      <!-- ══════════════════════════════════
           TAB 2 – THERAPY RECORDS REPORT
      ══════════════════════════════════ -->
      <div class="tab-panel<?= $active_tab === 'therapy' ? ' active' : '' ?>" id="panel-therapy">

        <!-- Filters -->
        <div class="therapy-filter-row">
          <div class="filter-field">
            <label class="filter-label">Healthcare Provider</label>
            <select class="filter-select" id="th_provider">
              <option>All Providers</option>
              <option>Dr. Jose Rizal</option>
              <option>Dr. Miguel Torres</option>
            </select>
          </div>
          <div class="filter-field">
            <label class="filter-label">Date Range</label>
            <input type="date" class="filter-input" id="th_date" value="2026-03-01">
          </div>
          <div class="filter-field">
            <label class="filter-label">Therapy Type</label>
            <select class="filter-select" id="th_type">
              <option>All Types</option>
              <option>Physical Therapy</option>
              <option>Occupational Therapy</option>
              <option>Speech Therapy</option>
            </select>
          </div>
        </div>

        <!-- Table -->
        <table id="therapyTable">
          <thead>
            <tr>
              <th>Patient Name</th>
              <th>Therapy Type</th>
              <th>Sessions Completed</th>
              <th>Status</th>
              <th>Provider</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($therapy_records as $r): ?>
              <tr>
                <td class="patient-bold"><?= htmlspecialchars($r['patient']) ?></td>
                <td class="therapy-type"><?= htmlspecialchars($r['type']) ?></td>
                <td><?= htmlspecialchars($r['sessions']) ?></td>
                <td>
                  <span class="badge <?= $r['status'] === 'Completed' ? 'badge-completed' : 'badge-inprogress' ?>">
                    <?= htmlspecialchars($r['status']) ?>
                  </span>
                </td>
                <td class="provider-name"><?= htmlspecialchars($r['provider']) ?></td>
                <td><?= htmlspecialchars($r['date']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div><!-- /panel-therapy -->


      <!-- ══════════════════════════════════
           TAB 3 – ACTIVITY LOG
      ══════════════════════════════════ -->
      <div class="tab-panel<?= $active_tab === 'activity' ? ' active' : '' ?>" id="panel-activity">

        <!-- Search bar -->
        <div class="search-bar-wrap">
          <?= icon('search', $svg) ?>
          <input type="text" class="search-bar" id="logSearch"
                 placeholder="Search by user or action..."
                 oninput="filterLog(this.value)">
        </div>

        <!-- Log entries -->
        <div class="log-list" id="logList">
          <?php foreach ($activity_log as $entry): ?>
            <div class="log-entry<?= $entry['highlight'] ? ' highlight' : '' ?>"
                 data-action="<?= htmlspecialchars(strtolower($entry['action'])) ?>"
                 data-actor="<?= htmlspecialchars(strtolower($entry['actor'])) ?>">
              <div class="log-icon-wrap">
                <?= icon('log-icon', $svg) ?>
              </div>
              <div class="log-body">
                <div class="log-action"><?= htmlspecialchars($entry['action']) ?></div>
                <div class="log-meta">
                  <?= htmlspecialchars($entry['actor']) ?>
                  <span class="sep">•</span>
                  <span class="log-time"><?= htmlspecialchars($entry['time']) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

      </div><!-- /panel-activity -->

    </div><!-- /panel -->

  </div><!-- /content -->
</div><!-- /main -->


<script>
  // ── Tab switching ──────────────────────
  function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    history.replaceState(null, '', url);
  }

  // ── Chart.js – Emergency Alerts Trend ─
  (function () {
    const ctx = document.getElementById('chartAlerts');
    if (!ctx) return;
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
          data: [35, 32, 46],
          backgroundColor: '#e05a2b',
          borderRadius: 4,
          barPercentage: 0.55,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { font: { size: 11 } } },
          y: { beginAtZero: true, max: 60,
               ticks: { stepSize: 15, font: { size: 11 } },
               grid: { color: '#eaf6f4' } }
        }
      }
    });
  })();

  // ── Chart.js – User Registration Trend ─
  (function () {
    const ctx = document.getElementById('chartUsers');
    if (!ctx) return;
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar'],
        datasets: [{
          data: [29, 32, 38],
          borderColor: '#2e8b7a',
          backgroundColor: 'rgba(46,139,122,.08)',
          borderWidth: 2.5,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#2e8b7a',
          pointBorderWidth: 2,
          pointRadius: 5,
          tension: 0.35,
          fill: true,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { font: { size: 11 } } },
          y: { beginAtZero: true, max: 60,
               ticks: { stepSize: 15, font: { size: 11 } },
               grid: { color: '#eaf6f4' } }
        }
      }
    });
  })();

  // ── Generate report (front-end stub) ──
  function generateReport() {
    const type  = document.getElementById('sum_type').value;
    const start = document.getElementById('sum_start').value;
    const end   = document.getElementById('sum_end').value;
    alert(`Generating report: ${type}\nFrom: ${start} to ${end}\n\nReport generation will be available once the database is connected.`);
  }

  // ── Download buttons (front-end stubs) ─
  function downloadReport(format) {
    alert(`Download as ${format.toUpperCase()} will be available once the database is connected.`);
  }

  // ── Activity log search ────────────────
  function filterLog(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('#logList .log-entry').forEach(entry => {
      const action = entry.dataset.action || '';
      const actor  = entry.dataset.actor  || '';
      entry.style.display = (action.includes(q) || actor.includes(q)) ? '' : 'none';
    });
  }
</script>

</body>
</html>