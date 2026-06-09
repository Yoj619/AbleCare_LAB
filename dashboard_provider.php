<?php
// ============================================================
//  AbleCare – Healthcare Provider Dashboard
// ============================================================
session_start();

// Guard: only healthcare_provider may access this page
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'healthcare_provider') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare – Provider Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --teal:      #3aafa9;
      --teal-dark: #2b9e98;
      --teal-pale: #e8f7f6;
      --sidebar:   #0c2233;
      --sidebar-hover: #153045;
      --white:     #ffffff;
      --dark:      #1e2a2a;
      --text:      #374151;
      --muted:     #6b7280;
      --bg:        #f0f7f6;
      --border:    #e0eded;
      --provider-badge: #0891b2;
    }

    body {
      font-family: 'Open Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 240px;
      min-height: 100vh;
      background: var(--sidebar);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0;
      z-index: 50;
    }

    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 24px 20px;
      border-bottom: 1px solid #1a3a50;
    }

    .sidebar-brand span {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.1rem;
      color: #fff;
    }

    .sidebar-nav { flex: 1; padding: 16px 0; }

    .nav-label {
      font-size: 0.68rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #4a7090;
      padding: 12px 20px 6px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 20px;
      color: #8ab0c8;
      text-decoration: none;
      font-size: 0.9rem;
      transition: background 0.15s, color 0.15s;
      border-left: 3px solid transparent;
    }

    .nav-item:hover,
    .nav-item.active {
      background: var(--sidebar-hover);
      color: #fff;
      border-left-color: var(--teal);
    }

    .nav-item svg {
      width: 18px; height: 18px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
      flex-shrink: 0;
    }

    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid #1a3a50;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .user-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: var(--teal);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 0.95rem;
      color: #fff;
      flex-shrink: 0;
    }

    .user-meta { overflow: hidden; }

    .user-meta strong {
      display: block;
      font-size: 0.82rem;
      color: #e0f0f0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .user-meta span {
      font-size: 0.72rem;
      color: #5a8aaa;
    }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding: 9px 14px;
      background: rgba(229,57,53,0.12);
      border: 1px solid rgba(229,57,53,0.25);
      border-radius: 8px;
      color: #ef9a9a;
      font-size: 0.85rem;
      font-family: 'Open Sans', sans-serif;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.15s;
    }

    .btn-logout:hover { background: rgba(229,57,53,0.22); }

    .btn-logout svg {
      width: 16px; height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* ── MAIN ── */
    .main {
      margin-left: 240px;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .topbar {
      background: var(--white);
      border-bottom: 1px solid var(--border);
      padding: 16px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .topbar h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--dark);
    }

    .badge-role {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      background: #e0f2fe;
      color: var(--provider-badge);
    }

    .content { flex: 1; padding: 32px; }

    /* Welcome banner */
    .welcome-banner {
      background: linear-gradient(135deg, #0c7e99 0%, #0a6280 100%);
      border-radius: 16px;
      padding: 28px 32px;
      color: #fff;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .welcome-banner h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.45rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .welcome-banner p { font-size: 0.88rem; opacity: 0.88; }

    .welcome-banner svg {
      width: 64px; height: 64px;
      opacity: 0.2;
      stroke: #fff;
      fill: none;
      stroke-width: 1.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Quick-action cards */
    .cards-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }

    .quick-card {
      background: var(--white);
      border-radius: 14px;
      border: 1px solid var(--border);
      padding: 24px;
      text-align: center;
      cursor: pointer;
      transition: box-shadow 0.2s, transform 0.2s;
    }

    .quick-card:hover {
      box-shadow: 0 6px 24px rgba(58,175,169,0.12);
      transform: translateY(-2px);
    }

    .quick-icon {
      width: 56px; height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
    }

    .quick-icon svg {
      width: 28px; height: 28px;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .quick-icon.teal { background: var(--teal-pale); }
    .quick-icon.teal svg { stroke: var(--teal); }

    .quick-icon.blue { background: #e0f2fe; }
    .quick-icon.blue svg { stroke: var(--provider-badge); }

    .quick-icon.green { background: #d1fae5; }
    .quick-icon.green svg { stroke: #059669; }

    .quick-card h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 6px;
    }

    .quick-card p { font-size: 0.80rem; color: var(--muted); line-height: 1.5; }

    /* Info panel */
    .info-panel {
      background: var(--white);
      border-radius: 14px;
      border: 1px solid var(--border);
      padding: 28px 32px;
    }

    .info-panel h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 18px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border);
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .info-item label {
      display: block;
      font-size: 0.73rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      margin-bottom: 4px;
    }

    .info-item span {
      font-size: 0.9rem;
      color: var(--dark);
      font-weight: 600;
    }

    @media (max-width: 900px) {
      .sidebar { width: 200px; }
      .main { margin-left: 200px; }
      .cards-row { grid-template-columns: 1fr 1fr; }
      .info-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
      .sidebar { display: none; }
      .main { margin-left: 0; }
      .cards-row { grid-template-columns: 1fr; }
      .content { padding: 16px; }
    }
  </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="ablecarelogo.png" alt="AbleCare" style="width:32px;height:32px;object-fit:contain;">
    <span>AbleCare</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Main</div>
    <a href="dashboard_provider.php" class="nav-item active">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>

    <div class="nav-label">Healthcare</div>
    <a href="#" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
      My Patients
    </a>
    <a href="#" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M22 16.92V19a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81"/><path d="M22 16.92V19a2 2 0 01-2.18 2"/></svg>
      Emergency Alerts
    </a>
    <a href="#" class="nav-item">
      <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      Appointments
    </a>
    <a href="#" class="nav-item">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>
      Settings
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
      <div class="user-meta">
        <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>
        <span>Healthcare Provider</span>
      </div>
    </div>
    <a href="logout.php" class="btn-logout">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </div>
</aside>

<!-- ── MAIN ── -->
<div class="main">

  <div class="topbar">
    <h1>Provider Dashboard</h1>
    <div>
      <span class="badge-role">Healthcare Provider</span>
    </div>
  </div>

  <div class="content">

    <!-- Welcome banner -->
    <div class="welcome-banner">
      <div>
        <h2>Welcome, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?>! 🩺</h2>
        <p>Manage your patients and respond to emergency alerts from this dashboard.</p>
      </div>
      <svg viewBox="0 0 24 24">
        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
      </svg>
    </div>

    <!-- Quick-action cards -->
    <div class="cards-row">
      <div class="quick-card">
        <div class="quick-icon teal">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <h3>View Patients</h3>
        <p>Browse and manage your assigned patients' health records.</p>
      </div>

      <div class="quick-card">
        <div class="quick-icon blue">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        </div>
        <h3>Emergency Alerts</h3>
        <p>Respond quickly to active emergency notifications from caregivers.</p>
      </div>

      <div class="quick-card">
        <div class="quick-icon green">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15h6"/></svg>
        </div>
        <h3>First Aid Logs</h3>
        <p>Review AI first-aid guidance sessions submitted by caregivers.</p>
      </div>
    </div>

    <!-- Account info -->
    <div class="info-panel">
      <h3>My Account Information</h3>
      <div class="info-grid">
        <div class="info-item">
          <label>Full Name</label>
          <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        </div>
        <div class="info-item">
          <label>Email Address</label>
          <span><?= htmlspecialchars($_SESSION['email']) ?></span>
        </div>
        <div class="info-item">
          <label>Role</label>
          <span>Healthcare Provider</span>
        </div>
        <div class="info-item">
          <label>Account Status</label>
          <span style="color:#059669;">● Active</span>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

</body>
</html>
