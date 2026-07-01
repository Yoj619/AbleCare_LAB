<?php
/**
 * AbleCare - Healthcare Provider Portal
 * Dashboard Page
 *
 * TODO (Database Integration):
 * - Replace the $stats, $consultationRequests, $therapySchedule arrays below
 *   with actual DB queries (e.g., PDO or MySQLi).
 * - Connect session/auth to verify logged-in provider.
 * - "View All" links point to their respective full-list pages
 *   (consultation_requests.php, therapy_schedule.php, etc.)
 * - Action handlers (accept/decline) should POST to an API endpoint
 *   or the same page with action logic at the top.
 */

// -----------------------------------------------------------------------
// Dashboard data (replace with DB queries when provider auth + tables exist)
// -----------------------------------------------------------------------

$provider = [
    'name'       => $_SESSION['full_name'] ?? 'Healthcare Provider',
    'role'       => $_SESSION['role'] ?? 'Healthcare Provider',
    'hospital'   => $_SESSION['hospital'] ?? '',
    'department' => $_SESSION['department'] ?? '',
    'avatar'     => $_SESSION['avatar'] ?? '',
];

// TODO: replace with DB queries (counts of active patients / pending consults / today's sessions) when tables exist
$stats = [
    'active_patients'    => ['count' => 0, 'change' => null, 'label' => 'Active Patients'],
    'pending_consults'   => ['count' => 0, 'change' => null, 'label' => 'Pending Consultation Requests', 'badge' => 0],
    'therapy_sessions'   => ['count' => 0, 'change' => null, 'label' => "Today's Therapy Sessions"],
];

// TODO: replace with DB query (SELECT * FROM consultation_requests WHERE status='pending' ORDER BY date_requested DESC LIMIT 5) when table exists
$consultationRequests = [];

// TODO: replace with DB query (SELECT * FROM therapy_sessions WHERE session_date = CURDATE() ORDER BY session_time ASC) when table exists
$therapySchedule = [];

// Hour-based greeting
$hour = (int) date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
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
                                <td><strong><?= htmlspecialchars($req['patient_name']) ?></strong></td>
                                <td><span class="caregiver-link"><?= htmlspecialchars($req['caregiver']) ?></span></td>
                                <td><?= htmlspecialchars($req['condition']) ?></td>
                                <td><?= htmlspecialchars($req['date_requested']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="action-btn action-accept"
                                                title="Accept"
                                                onclick="openAcceptModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['patient_name'])) ?>')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#26a69a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="9 12 11 14 15 10"/>
                                            </svg>
                                        </button>

                                        <button class="action-btn action-view"
                                                title="View Details"
                                                onclick="openViewModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['patient_name'])) ?>', '<?= htmlspecialchars(addslashes($req['caregiver'])) ?>', '<?= htmlspecialchars(addslashes($req['condition'])) ?>', '<?= htmlspecialchars(addslashes($req['date_requested'])) ?>')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#5c9bd6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>

                                        <button class="action-btn action-decline"
                                                title="Decline"
                                                onclick="openDeclineModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['patient_name'])) ?>')">
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
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalView')">&times;</button>
        <h3>Consultation Request Details</h3>
        <div class="detail-field">
            <div class="detail-label">Patient Name</div>
            <div class="detail-value" id="viewPatientName"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Caregiver</div>
            <div class="detail-value" id="viewCaregiver"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Condition Summary</div>
            <div class="detail-value" id="viewCondition"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Date Requested</div>
            <div class="detail-value" id="viewDateRequested"></div>
        </div>
        <div class="modal-actions">
            <button class="btn-close-detail" onclick="closeModal('modalView')">Close</button>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- JAVASCRIPT — Modal Logic (Frontend Only)                         -->
<!-- ================================================================ -->
<script>
    let currentRequestId = null;

    function openAcceptModal(id, patientName) {
        currentRequestId = id;
        document.getElementById('acceptPatientName').textContent = patientName;
        document.getElementById('modalAccept').classList.add('open');
    }

    function confirmAccept() {
        alert('Accepted request ID: ' + currentRequestId + '\n(TODO: connect to backend)');
        closeModal('modalAccept');
    }

    function openDeclineModal(id, patientName) {
        currentRequestId = id;
        document.getElementById('declinePatientName').textContent = patientName;
        document.getElementById('declineReason').value = '';
        document.getElementById('modalDecline').classList.add('open');
    }

    function confirmDecline() {
        const reason = document.getElementById('declineReason').value;
        alert('Declined request ID: ' + currentRequestId + '\nReason: ' + (reason || 'None') + '\n(TODO: connect to backend)');
        closeModal('modalDecline');
    }

    function openViewModal(id, patientName, caregiver, condition, dateRequested) {
        document.getElementById('viewPatientName').textContent   = patientName;
        document.getElementById('viewCaregiver').textContent     = caregiver;
        document.getElementById('viewCondition').textContent     = condition;
        document.getElementById('viewDateRequested').textContent = dateRequested;
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
</script>
</body>
</html>