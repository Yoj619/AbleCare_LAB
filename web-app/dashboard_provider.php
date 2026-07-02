<?php
// ============================================================
//  AbleCare – Healthcare Provider Dashboard
// ============================================================
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'healthcare_provider') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
$db = get_db();

// ── Resolve this provider's healthcare_providers.id ──────────────────────────
$stmt = $db->prepare('SELECT hp.id, cl.name AS clinic_name
                      FROM healthcare_providers hp
                      LEFT JOIN clinics cl ON cl.id = hp.clinic_id
                      WHERE hp.user_id = ? LIMIT 1');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$hpRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$hpRow) {
    header('Location: provider_settings.php');
    exit;
}
$hpId = (int) $hpRow['id'];

// ── Handle Approve / Decline POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consult_action'])) {
    $consultId = (int) ($_POST['consult_id'] ?? 0);
    $action    = $_POST['consult_action'];

    if ($consultId > 0 && in_array($action, ['accept', 'decline'], true)) {
        if ($action === 'accept') {
            // Fetch caregiver user_id before updating so we can send the welcome message
            $infoStmt = $db->prepare(
                'SELECT cg.user_id AS cg_user_id
                 FROM consultations c
                 JOIN caregivers cg ON cg.id = c.caregiver_id
                 WHERE c.id = ? AND c.healthcare_provider_id = ? AND c.status = "pending" LIMIT 1'
            );
            $infoStmt->bind_param('ii', $consultId, $hpId);
            $infoStmt->execute();
            $consultInfo = $infoStmt->get_result()->fetch_assoc();
            $infoStmt->close();

            $stmt = $db->prepare(
                "UPDATE consultations SET status = 'accepted'
                 WHERE id = ? AND healthcare_provider_id = ? AND status = 'pending'"
            );
            $stmt->bind_param('ii', $consultId, $hpId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            // Auto-send welcome message and notification to the caregiver
            if ($affected > 0 && $consultInfo) {
                $cgUserId       = (int) $consultInfo['cg_user_id'];
                $providerUserId = (int) $_SESSION['user_id'];

                $msgText = 'Hello! Your consultation request has been approved. Feel free to message me here if you have any questions about your patient\'s care.';
                $msgStmt = $db->prepare(
                    'INSERT INTO messages (sender_id, receiver_id, message_text, is_read, sent_at)
                     VALUES (?, ?, ?, 0, NOW())'
                );
                $msgStmt->bind_param('iis', $providerUserId, $cgUserId, $msgText);
                $msgStmt->execute();
                $msgStmt->close();

                $notifTitle = 'Consultation Approved';
                $notifMsg   = 'Your consultation request has been approved. You now have a new message from your healthcare provider.';
                $notifStmt  = $db->prepare(
                    'INSERT INTO notifications (user_id, title, message, type, is_read)
                     VALUES (?, ?, ?, "message", 0)'
                );
                $notifStmt->bind_param('iss', $cgUserId, $notifTitle, $notifMsg);
                $notifStmt->execute();
                $notifStmt->close();
            }
        } else {
            $notes = trim($_POST['decline_reason'] ?? '');
            $stmt  = $db->prepare(
                "UPDATE consultations SET status = 'declined', notes = ?
                 WHERE id = ? AND healthcare_provider_id = ? AND status = 'pending'"
            );
            $stmt->bind_param('sii', $notes, $consultId, $hpId);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: dashboard_provider.php?action=' . urlencode($action));
    exit;
}

// ── Stats ─────────────────────────────────────────────────────────────────────
$r = $db->query("SELECT COUNT(*) AS n FROM consultations
                 WHERE healthcare_provider_id = $hpId AND status = 'pending'");
$pendingCount = $r ? (int) $r->fetch_assoc()['n'] : 0;

$r = $db->query("SELECT COUNT(*) AS n FROM consultations
                 WHERE healthcare_provider_id = $hpId AND status = 'accepted'");
$activePatients = $r ? (int) $r->fetch_assoc()['n'] : 0;

$stats = [
    'active_patients'  => ['count' => $activePatients, 'change' => null, 'label' => 'Active Patients'],
    'pending_consults' => ['count' => $pendingCount,   'change' => null, 'label' => 'Pending Consultation Requests', 'badge' => $pendingCount],
    'therapy_sessions' => ['count' => 0,               'change' => null, 'label' => "Today's Therapy Sessions"],
];

// ── Pending consultation requests (up to 5 for dashboard preview) ─────────────
$consultResult = $db->query("
    SELECT c.id, c.created_at,
           p.first_name AS pt_first, p.last_name AS pt_last,
           p.specific_condition, p.disability_category,
           uc.first_name AS cg_first, uc.last_name AS cg_last, uc.phone_number AS cg_phone,
           cg.address AS cg_address, cg.barangay AS cg_barangay
    FROM consultations c
    JOIN patients   p  ON p.id  = c.patient_id
    JOIN caregivers cg ON cg.id = c.caregiver_id
    JOIN users      uc ON uc.id = cg.user_id
    WHERE c.healthcare_provider_id = $hpId AND c.status = 'pending'
    ORDER BY c.created_at DESC
    LIMIT 5
");
$consultationRequests = $consultResult ? $consultResult->fetch_all(MYSQLI_ASSOC) : [];

// ── Provider display info ─────────────────────────────────────────────────────
$provider = [
    'name'       => $_SESSION['full_name'] ?? 'Healthcare Provider',
    'role'       => 'Healthcare Provider',
    'hospital'   => $hpRow['clinic_name'] ?? '',
    'department' => '',
    'avatar'     => '',
];

$therapySchedule = [];

$hour      = (int) date('H');
$greeting  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$actionMsg = $_GET['action'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AbleCare — Healthcare Provider Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/provider_dashboard.css">
</head>
<body>
<div class="layout">

    <!-- ================================================================ -->
    <!-- SIDEBAR                                                          -->
    <!-- ================================================================ -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:56px;height:56px;object-fit:contain;border-radius:10px;">
            <div class="brand-text">
                <span class="brand-name">AbleCare</span>
                <span class="brand-sub">Healthcare Provider</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard_provider.php" class="nav-item active">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>
                Dashboard
            </a>
            <a href="mypatients.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></span>
                My Patients
            </a>
            <a href="consultation_requests.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>
                Consultation Requests
            </a>
            <a href="therapy_schedule.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></span>
                Therapy Schedules
            </a>
            <a href="message.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg></span>
                Messages
            </a>
            <a href="provider_settings.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg></span>
                Account Settings
            </a>
        </nav>

        <div class="sidebar-logout">
            <a href="logout.php">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- ================================================================ -->
    <!-- MAIN                                                             -->
    <!-- ================================================================ -->
    <div class="main">

        <!-- Top Bar -->
        <header class="topbar">
            <div>
                <div class="topbar-title">Healthcare Provider Portal</div>
                <div class="topbar-sub"><?= htmlspecialchars($provider['hospital']) ?></div>
            </div>
            <div class="topbar-user">
                <div class="topbar-user-info">
                    <div class="topbar-user-name"><?= htmlspecialchars($provider['name']) ?></div>
                    <div class="topbar-user-role"><?= htmlspecialchars($provider['role']) ?></div>
                </div>
                <img src="<?= $provider['avatar'] ?>" alt="Avatar" class="avatar" />
            </div>
        </header>

        <!-- Content -->
        <div class="content">

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2>
                    <svg viewBox="0 0 24 24" fill="white"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    <?= $greeting ?>, <?= htmlspecialchars($provider['name']) ?>
                </h2>
                <p><?= htmlspecialchars($provider['hospital']) ?> — <?= htmlspecialchars($provider['department']) ?></p>
                <div class="status">
                    <span class="status-dot"></span>
                    All systems operational &bull; Last updated just now
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <!-- Active Patients -->
                <div class="stat-card">
                    <div class="stat-icon-wrap teal">
                        <svg viewBox="0 0 24 24" fill="#26a69a"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    </div>
                    <div class="stat-label"><?= $stats['active_patients']['label'] ?></div>
                    <div class="stat-count"><?= $stats['active_patients']['count'] ?></div>
                    <?php if ($stats['active_patients']['change'] !== null): ?>
                    <div class="stat-change">↗ <span><?= htmlspecialchars($stats['active_patients']['change']) ?></span></div>
                    <?php endif; ?>
                </div>
                <!-- Pending Consultations -->
                <div class="stat-card">
                    <?php if (!empty($stats['pending_consults']['badge'])): ?>
                    <div class="stat-badge"><?= $stats['pending_consults']['badge'] ?></div>
                    <?php endif; ?>
                    <div class="stat-icon-wrap blue">
                        <svg viewBox="0 0 24 24" fill="#5c9bd6"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    </div>
                    <div class="stat-label"><?= $stats['pending_consults']['label'] ?></div>
                    <div class="stat-count"><?= $stats['pending_consults']['count'] ?></div>
                    <?php if ($stats['pending_consults']['change'] !== null): ?>
                    <div class="stat-change">↗ <span><?= htmlspecialchars($stats['pending_consults']['change']) ?></span></div>
                    <?php endif; ?>
                </div>
                <!-- Today's Sessions -->
                <div class="stat-card">
                    <div class="stat-icon-wrap orange">
                        <svg viewBox="0 0 24 24" fill="#f57c00"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                    </div>
                    <div class="stat-label"><?= $stats['therapy_sessions']['label'] ?></div>
                    <div class="stat-count"><?= $stats['therapy_sessions']['count'] ?></div>
                    <?php if ($stats['therapy_sessions']['change'] !== null): ?>
                    <div class="stat-change">↗ <span><?= htmlspecialchars($stats['therapy_sessions']['change']) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Consultation Requests -->
            <div class="card">
                <div class="card-inner">
                    <div class="section-header">
                        <div>
                            <div class="section-title">Pending Consultation Requests</div>
                            <div class="section-sub">Review and respond to patient consultation requests</div>
                        </div>
                        <a href="consultation_requests.php" class="btn-view-all">
                            View All
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                        </a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Caregiver</th>
                                <th>Condition Summary</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($consultationRequests)): ?>
                            <tr><td colspan="5">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <p>No pending consultation requests.</p>
                                </div>
                            </td></tr>
                        <?php else: ?>
                        <?php foreach ($consultationRequests as $req): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($req['pt_first'] . ' ' . $req['pt_last']) ?></strong></td>
                                <td><span class="caregiver-link"><?= htmlspecialchars($req['cg_first'] . ' ' . $req['cg_last']) ?></span></td>
                                <td><?= htmlspecialchars($req['specific_condition'] ?: ucfirst(str_replace('_', ' ', $req['disability_category'] ?? ''))) ?></td>
                                <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="action-btn action-accept"
                                                title="Approve"
                                                onclick="openAcceptModal(<?= (int)$req['id'] ?>, '<?= htmlspecialchars(addslashes($req['pt_first'] . ' ' . $req['pt_last'])) ?>')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#26a69a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="9 12 11 14 15 10"/>
                                            </svg>
                                        </button>

                                        <button class="action-btn action-view"
                                                title="View Details"
                                                onclick="openViewModal(<?= (int)$req['id'] ?>)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#5c9bd6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>

                                        <button class="action-btn action-decline"
                                                title="Decline"
                                                onclick="openDeclineModal(<?= (int)$req['id'] ?>, '<?= htmlspecialchars(addslashes($req['pt_first'] . ' ' . $req['pt_last'])) ?>')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#ef5350" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <line x1="15" y1="9" x2="9" y2="15"/>
                                                <line x1="9" y1="9" x2="15" y2="15"/>
                                            </svg>
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

            <!-- Today's Therapy Schedule -->
            <div class="card">
                <div class="card-inner" style="padding-bottom: 16px;">
                    <div class="section-header">
                        <div>
                            <div class="section-title">Today's Therapy Schedule</div>
                            <div class="section-sub"><?= count($therapySchedule) ?> sessions scheduled</div>
                        </div>
                        <a href="therapy_schedule.php" class="link-view-all">
                            View All
                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                        </a>
                    </div>
                </div>
                <div class="schedule-list">
                    <?php if (empty($therapySchedule)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p>No therapy sessions scheduled for today.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($therapySchedule as $session): ?>
                    <div class="schedule-item">
                        <div class="schedule-time"><?= htmlspecialchars($session['time']) ?></div>
                        <div class="schedule-info">
                            <div class="schedule-patient"><?= htmlspecialchars($session['patient']) ?></div>
                            <div class="schedule-type"><?= htmlspecialchars($session['therapy_type']) ?></div>
                        </div>
                        <span class="status-pill status-<?= $session['status'] ?>">
                            <?= ucfirst($session['status']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- .content -->
    </div><!-- .main -->
</div><!-- .layout -->

<!-- ================================================================ -->
<!-- MODALS                                                           -->
<!-- ================================================================ -->

<!-- Accept Confirmation Modal -->
<div class="modal-overlay" id="modalAccept">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalAccept')">&times;</button>
        <h3>Accept Consultation Request</h3>
        <p>Are you sure you want to accept the consultation request for <strong id="acceptPatientName"></strong>?</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalAccept')">Cancel</button>
            <button class="btn-confirm-accept" onclick="confirmAccept()">Confirm Accept</button>
        </div>
    </div>
</div>

<!-- Decline Confirmation Modal -->
<div class="modal-overlay" id="modalDecline">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalDecline')">&times;</button>
        <h3>Decline Consultation Request</h3>
        <p>Are you sure you want to decline the consultation request for <strong id="declinePatientName"></strong>?</p>
        <label class="decline-reason-label">Reason for Declining (Optional)</label>
        <textarea class="decline-textarea" id="declineReason" placeholder="Enter reason for declining this request..."></textarea>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalDecline')">Cancel</button>
            <button class="btn-confirm-decline" onclick="confirmDecline()">Confirm Decline</button>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal-overlay" id="modalView">
    <div class="modal" style="max-width:560px;">
        <button class="modal-close" onclick="closeModal('modalView')">&times;</button>
        <h3>Consultation Request Details</h3>
        <div id="viewModalBody"></div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalView')">Close</button>
            <button class="btn-confirm-accept" onclick="closeModal('modalView');openAcceptModal(viewCurrentId,viewCurrentPatient)">Approve</button>
            <button class="btn-confirm-decline" onclick="closeModal('modalView');openDeclineModal(viewCurrentId,viewCurrentPatient)">Decline</button>
        </div>
    </div>
</div>

<!-- Hidden form for approve/decline POST -->
<form id="consultActionForm" method="POST" action="dashboard_provider.php" style="display:none;">
    <input type="hidden" name="consult_id"      id="hiddenConsultId">
    <input type="hidden" name="consult_action"  id="hiddenAction">
    <input type="hidden" name="decline_reason"  id="hiddenDeclineReason" value="">
</form>

<!-- ================================================================ -->
<!-- JAVASCRIPT                                                        -->
<!-- ================================================================ -->
<script>
    // Consultation data keyed by ID (for the view modal)
    var CONSULT_DATA = <?= json_encode(
        array_column(array_map(function($r) {
            return [
                'id'          => (int) $r['id'],
                'patientName' => $r['pt_first'] . ' ' . $r['pt_last'],
                'condition'   => $r['specific_condition'] ?: ucfirst(str_replace('_', ' ', $r['disability_category'] ?? '')),
                'cgName'      => $r['cg_first'] . ' ' . $r['cg_last'],
                'cgPhone'     => $r['cg_phone'] ?? '',
                'cgAddress'   => trim(($r['cg_address'] ?? '') . ($r['cg_barangay'] ? ', Brgy. ' . $r['cg_barangay'] : '')),
                'date'        => date('M d, Y · g:i A', strtotime($r['created_at'])),
            ];
        }, $consultationRequests), null, 'id'),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;

    <?php if ($actionMsg !== ''): ?>
    (function() {
        const msg = document.createElement('div');
        msg.textContent = '<?= $actionMsg === 'accept' ? 'Request approved.' : 'Request declined.' ?>';
        msg.style.cssText = 'position:fixed;top:16px;right:20px;z-index:9999;background:<?= $actionMsg === 'accept' ? '#26a69a' : '#ef5350' ?>;color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.18);';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3500);
    })();
    <?php endif; ?>

    let currentRequestId = null;
    let viewCurrentId    = null;
    let viewCurrentPatient = '';

    function ve(str) {
        var d = document.createElement('div');
        d.textContent = str || '—';
        return d.innerHTML;
    }

    function openAcceptModal(id, patientName) {
        currentRequestId = id;
        document.getElementById('acceptPatientName').textContent = patientName;
        document.getElementById('modalAccept').classList.add('open');
    }

    function confirmAccept() {
        document.getElementById('hiddenConsultId').value      = currentRequestId;
        document.getElementById('hiddenAction').value         = 'accept';
        document.getElementById('hiddenDeclineReason').value  = '';
        document.getElementById('consultActionForm').submit();
    }

    function openDeclineModal(id, patientName) {
        currentRequestId = id;
        document.getElementById('declinePatientName').textContent = patientName;
        document.getElementById('declineReason').value = '';
        document.getElementById('modalDecline').classList.add('open');
    }

    function confirmDecline() {
        document.getElementById('hiddenConsultId').value      = currentRequestId;
        document.getElementById('hiddenAction').value         = 'decline';
        document.getElementById('hiddenDeclineReason').value  = document.getElementById('declineReason').value;
        document.getElementById('consultActionForm').submit();
    }

    function openViewModal(id) {
        var d = CONSULT_DATA[id];
        if (!d) return;
        viewCurrentId      = id;
        viewCurrentPatient = d.patientName;

        document.getElementById('viewModalBody').innerHTML =
            '<div class="detail-field"><div class="detail-label">Patient</div><div class="detail-value">' + ve(d.patientName) + '</div></div>' +
            '<div class="detail-field"><div class="detail-label">Condition / Disability</div><div class="detail-value">' + ve(d.condition) + '</div></div>' +
            '<div class="detail-field"><div class="detail-label">Caregiver</div><div class="detail-value">' + ve(d.cgName) + '</div></div>' +
            '<div class="detail-field"><div class="detail-label">Caregiver Phone</div><div class="detail-value">' + ve(d.cgPhone) + '</div></div>' +
            '<div class="detail-field"><div class="detail-label">Caregiver Address</div><div class="detail-value">' + ve(d.cgAddress) + '</div></div>' +
            '<div class="detail-field"><div class="detail-label">Date Requested</div><div class="detail-value">' + ve(d.date) + '</div></div>';

        document.getElementById('modalView').classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['modalAccept','modalDecline','modalView'].forEach(closeModal);
        }
    });
</script>
</body>
</html>