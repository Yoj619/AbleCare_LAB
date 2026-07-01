<?php
// ============================================================
//  ABLECARE – System Notifications
//  Municipality of Nasugbu, Batangas
// ============================================================

session_start();

// ── Session guard (uncomment when DB is ready) ──
// if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'lgu_admin') {
//     header('Location: login.php');
//     exit;
// }

// ── Current logged-in admin (session-based, generic fallback until DB is wired) ──
$admin = [
    'name'   => $_SESSION['full_name'] ?? 'Administrator',
    'role'   => $_SESSION['role']      ?? 'lgu_admin',
    'avatar' => $_SESSION['avatar']    ?? '',
];

// ── Active tab ──
$active_tab = $_GET['tab'] ?? 'received';

// ── Nav items ──
$nav_items = [
    ['icon' => 'dashboard',       'label' => 'Dashboard',              'href' => 'dashboard_admin.php',   'active' => false],
    ['icon' => 'user-management', 'label' => 'User Management',        'href' => 'user_management.php',   'active' => false],
    ['icon' => 'facilities',      'label' => 'Healthcare Facilities',  'href' => 'facilities.php',        'active' => false],
    ['icon' => 'emergency',       'label' => 'Emergency Monitor',      'href' => 'emergency_monitor.php', 'active' => false],
    ['icon' => 'notifications',   'label' => 'System Notifications',   'href' => 'notifications.php',     'active' => true],
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
    'check'           => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>',
    'check-double'    => '<path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/>',
    'send'            => '<path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>',
    'save'            => '<path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>',
    'empty-notif'     => '<path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
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

// ── Notifications (replace with PDO query when DB is ready) ──
/*
$pdo = new PDO('mysql:host=localhost;dbname=ablecare', $db_user, $db_pass);

$stmt = $pdo->prepare("
    SELECT id, type, message, created_at, is_read
    FROM notifications
    WHERE recipient_id = :uid
    ORDER BY created_at DESC
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
*/

$notifications = [];  // TODO: replace with DB query (SELECT ... FROM notifications) when table exists

// ── Badge color map per type ──
$type_colors = [
    'Emergency Alert'    => ['bg' => '#fdeaea', 'color' => '#c0392b', 'dot' => '#e74c3c'],
    'New Registration'   => ['bg' => '#eaf6f4', 'color' => '#1f6b5e', 'dot' => '#2e8b7a'],
    'System Update'      => ['bg' => '#eef2ff', 'color' => '#3730a3', 'dot' => '#6366f1'],
    'Therapy Report'     => ['bg' => '#fffbeb', 'color' => '#92400e', 'dot' => '#f59e0b'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AbleCare – System Notifications</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/notif.css">
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
      <button class="notif-btn" title="Notifications">
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
    <div class="page-header-left">
      <h2>
        <?= icon('bell', $svg) ?>
        System Notifications
      </h2>
      <div class="breadcrumb">
        <a href="dashboard_admin.php">Dashboard</a>
        &rsaquo;
        <span>System Notifications</span>
      </div>
    </div>

    <!-- Tabbed panel -->
    <div class="panel">

      <!-- Tab buttons -->
      <div class="tabs-bar">
        <button class="tab-btn<?= $active_tab === 'received' ? ' active' : '' ?>"
                id="tab-received" onclick="switchTab('received')">
          Received Notifications
        </button>
        <button class="tab-btn<?= $active_tab === 'send' ? ' active' : '' ?>"
                id="tab-send" onclick="switchTab('send')">
          Send Notifications
        </button>
      </div>

      <!-- ── TAB: RECEIVED ──────────────── -->
      <div class="tab-panel<?= $active_tab === 'received' ? ' active' : '' ?>" id="panel-received">

        <?php if (!empty($notifications)): ?>
          <div class="mark-all-row">
            <button class="btn-mark-all" onclick="markAllRead()">
              <?= icon('check-double', $svg) ?>
              Mark All as Read
            </button>
          </div>
        <?php endif; ?>

        <div class="notif-list" id="notifList">
          <?php if (empty($notifications)): ?>
            <div class="empty-state">
              <?= icon('empty-notif', $svg) ?>
              <p>No notifications yet.</p>
            </div>
          <?php else: ?>
            <?php foreach ($notifications as $n):
              $type   = htmlspecialchars($n['type'] ?? 'General');
              $msg    = htmlspecialchars($n['message'] ?? '');
              $time   = htmlspecialchars($n['time_label'] ?? '');
              $unread = !($n['is_read'] ?? false);
              $colors = $type_colors[$n['type']] ?? ['bg' => '#f4f4f4', 'color' => '#555', 'dot' => '#aaa'];
            ?>
              <div class="notif-card <?= $unread ? 'unread' : 'read' ?>"
                   id="notif-<?= (int)$n['id'] ?>">
                <span class="notif-dot"
                      style="background:<?= $colors['dot'] ?>;"></span>
                <div class="notif-body">
                  <span class="notif-type-badge"
                        style="background:<?= $colors['bg'] ?>; color:<?= $colors['color'] ?>;">
                    <?= $type ?>
                  </span>
                  <div class="notif-message"><?= $msg ?></div>
                  <div class="notif-time"><?= $time ?></div>
                </div>
                <button class="btn-notif-read"
                        onclick="markRead(<?= (int)$n['id'] ?>)"
                        title="Mark as read">
                  <?= icon('check', $svg) ?>
                </button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div><!-- /panel-received -->


      <!-- ── TAB: SEND ──────────────────── -->
      <div class="tab-panel<?= $active_tab === 'send' ? ' active' : '' ?>" id="panel-send">
        <form class="send-form" method="POST" action="notifications.php?tab=send"
              onsubmit="return validateSendForm()">
          <input type="hidden" name="action" value="send_notification">

          <!-- Recipient Selection -->
          <div class="form-group">
            <label class="form-label" for="recipient_selection">Recipient Selection</label>
            <select class="form-select" id="recipient_selection" name="recipient_selection" required>
              <option value="" disabled selected></option>
              <option value="all">All Users</option>
              <option value="caregivers">All Caregivers</option>
              <option value="providers">All Healthcare Providers</option>
              <option value="admins">All Admins</option>
              <!-- Populate specific users from DB when ready -->
              <!-- <?php /*
              foreach ($users as $u):
                echo "<option value=\"{$u['id']}\">{$u['full_name']} ({$u['role']})</option>";
              endforeach;
              */ ?>-->
            </select>
          </div>

          <!-- Notification Type -->
          <div class="form-group">
            <label class="form-label" for="notification_type">Notification Type</label>
            <select class="form-select" id="notification_type" name="notification_type" required>
              <option value="" disabled selected></option>
              <option value="Emergency Alert">Emergency Alert</option>
              <option value="New Registration">New Registration</option>
              <option value="System Update">System Update</option>
              <option value="Therapy Report">Therapy Report</option>
              <option value="General">General</option>
            </select>
          </div>

          <!-- Subject Line -->
          <div class="form-group">
            <label class="form-label" for="subject_line">
              Subject Line
              <span class="char-count" id="subjectCount">(0/100)</span>
            </label>
            <input class="form-input" type="text" id="subject_line" name="subject_line"
                   placeholder="Enter notification subject..."
                   maxlength="100" required
                   oninput="updateCount('subject_line','subjectCount',100)">
          </div>

          <!-- Message Body -->
          <div class="form-group">
            <label class="form-label" for="message_body">Message Body</label>
            <textarea class="form-textarea" id="message_body" name="message_body"
                      placeholder="Enter notification message..."
                      rows="5" required></textarea>
          </div>

          <!-- Actions -->
          <div class="send-actions">
            <button type="submit" class="btn-send" name="submit_type" value="send">
              <?= icon('send', $svg) ?>
              Send Notification
            </button>
            <button type="submit" class="btn-draft" name="submit_type" value="draft">
              <?= icon('save', $svg) ?>
              Save as Draft
            </button>
          </div>

        </form>
      </div><!-- /panel-send -->

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

    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    history.replaceState(null, '', url);
  }

  // ── Mark single notification as read ──
  function markRead(id) {
    const card = document.getElementById('notif-' + id);
    if (card) {
      card.classList.remove('unread');
      card.classList.add('read');
    }
    /*
     * When DB is ready, send AJAX:
     * fetch('notifications.php', {
     *   method: 'POST',
     *   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
     *   body: `action=mark_read&id=${id}`
     * });
     */
  }

  // ── Mark all as read ──────────────────
  function markAllRead() {
    document.querySelectorAll('.notif-card.unread').forEach(card => {
      card.classList.remove('unread');
      card.classList.add('read');
    });
    /*
     * When DB is ready:
     * fetch('notifications.php', {
     *   method: 'POST',
     *   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
     *   body: 'action=mark_all_read'
     * });
     */
  }

  // ── Subject char counter ──────────────
  function updateCount(inputId, countId, max) {
    const len = document.getElementById(inputId).value.length;
    document.getElementById(countId).textContent = `(${len}/${max})`;
  }

  // ── Send form validation ──────────────
  function validateSendForm() {
    const recipient = document.getElementById('recipient_selection').value;
    const type      = document.getElementById('notification_type').value;
    const subject   = document.getElementById('subject_line').value.trim();
    const body      = document.getElementById('message_body').value.trim();
    if (!recipient || !type || !subject || !body) {
      alert('Please fill in all fields before sending.');
      return false;
    }
    return true;
  }
</script>

</body>
</html>
