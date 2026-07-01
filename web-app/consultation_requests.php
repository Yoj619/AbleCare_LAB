<?php
/**
 * AbleCare - Healthcare Provider Portal
 * Consultation Requests Page
 *
 * TODO (Database Integration):
 * - Replace the empty arrays below with actual DB queries (PDO/MySQLi).
 * - Connect session/auth to verify logged-in provider.
 * - Action handlers (accept/decline/view) should POST to API endpoints
 *   or the same page with action logic at the top.
 */

// -----------------------------------------------------------------------
// Consultation requests (replace with DB queries when provider auth + tables exist)
// -----------------------------------------------------------------------

$provider = [
    'name'     => $_SESSION['full_name'] ?? 'Healthcare Provider',
    'role'     => $_SESSION['role'] ?? 'Healthcare Provider',
    'hospital' => $_SESSION['hospital'] ?? '',
    'avatar'   => $_SESSION['avatar'] ?? '',
];

// TODO: replace with DB query (SELECT * FROM consultation_requests WHERE status='pending' ORDER BY date_requested DESC) when table exists
$pendingRequests = [];

// TODO: replace with DB query (SELECT * FROM consultation_requests WHERE status='accepted' ORDER BY date_requested DESC) when table exists
$acceptedRequests = [];

// TODO: replace with DB query (SELECT * FROM consultation_requests WHERE status='completed' ORDER BY date_completed DESC) when table exists
$completedRequests = [];

// TODO: replace with DB query (SELECT * FROM consultation_requests WHERE status='declined' ORDER BY date_declined DESC) when table exists
$declinedRequests = [];

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AbleCare — Consultation Requests</title>
    <link rel="stylesheet" href="css/consultation_requests.css">
</head>
<body>
<div class="layout">

    <!-- ================================================================ -->
    <!-- SIDEBAR (with logo only, no green background)                    -->
    <!-- ================================================================ -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:50px;height:auto; border-radius:10px;">
            <div class="brand-text">
                <span class="brand-name">AbleCare</span>
                <span class="brand-sub">Healthcare Provider</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard_provider.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>
                Dashboard
            </a>
            <a href="mypatients.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></span>
                My Patients
            </a>
            <a href="consultation_requests.php" class="nav-item active">
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

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-row">
                    <div class="page-title-icon">
                        <svg viewBox="0 0 24 24" fill="#26a69a"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                    </div>
                    <h1 class="page-title">Consultation Requests</h1>
                </div>
                <div class="breadcrumb">
                    <a href="dashboard_provider.php">Dashboard</a> &rsaquo; <span>Consultation Requests</span>
                </div>
            </div>

            <!-- Tabs Card -->
            <div class="tabs-card">
                <div class="tabs-nav">
                    <button class="tab-btn <?= $activeTab === 'pending'   ? 'active' : '' ?>"
                            onclick="switchTab('pending')">
                        Pending
                        <span class="tab-badge"><?= count($pendingRequests) ?></span>
                    </button>
                    <button class="tab-btn <?= $activeTab === 'accepted'  ? 'active' : '' ?>"
                            onclick="switchTab('accepted')">
                        Accepted
                        <span class="tab-badge"><?= count($acceptedRequests) ?></span>
                    </button>
                    <button class="tab-btn <?= $activeTab === 'completed' ? 'active' : '' ?>"
                            onclick="switchTab('completed')">
                        Completed
                        <span class="tab-badge"><?= count($completedRequests) ?></span>
                    </button>
                    <button class="tab-btn <?= $activeTab === 'declined'  ? 'active' : '' ?>"
                            onclick="switchTab('declined')">
                        Declined
                        <span class="tab-badge"><?= count($declinedRequests) ?></span>
                    </button>
                </div>

                <!-- ── PENDING TAB ──────────────────────────────────────── -->
                <div class="table-wrap" id="tab-pending"
                     style="<?= $activeTab !== 'pending' ? 'display:none' : '' ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Caregiver &amp; Contact</th>
                                <th>Request Date &amp; Time</th>
                                <th>Condition Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($pendingRequests)): ?>
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <p>No pending consultation requests.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($pendingRequests as $req): ?>
                            <tr>
                                <td><span class="patient-name"><?= htmlspecialchars($req['patient_name']) ?></span></td>
                                <td>
                                    <div class="caregiver-name"><?= htmlspecialchars($req['caregiver']) ?></div>
                                    <div class="caregiver-phone"><?= htmlspecialchars($req['caregiver_contact']) ?></div>
                                </td>
                                <td><span class="datetime-text"><?= htmlspecialchars($req['datetime']) ?></span></td>
                                <td><?= htmlspecialchars($req['condition']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="action-btn" title="Accept"
                                                onclick="openAcceptModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['patient_name'])) ?>')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#26a69a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="9 12 11 14 15 10"/>
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="View Details"
                                                onclick="openViewModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['patient_name'])) ?>', '<?= htmlspecialchars(addslashes($req['caregiver'])) ?>', '<?= htmlspecialchars(addslashes($req['caregiver_contact'])) ?>', '<?= htmlspecialchars(addslashes($req['datetime'])) ?>', '<?= htmlspecialchars(addslashes($req['condition'])) ?>')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="#5c9bd6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Decline"
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

                <!-- ── ACCEPTED TAB ─────────────────────────────────────── -->
                <div class="table-wrap" id="tab-accepted"
                     style="<?= $activeTab !== 'accepted' ? 'display:none' : '' ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Caregiver &amp; Contact</th>
                                <th>Request Date &amp; Time</th>
                                <th>Condition Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($acceptedRequests)): ?>
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <p>No accepted consultation requests.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($acceptedRequests as $req): ?>
                            <tr>
                                <td><span class="patient-name"><?= htmlspecialchars($req['patient_name']) ?></span></td>
                                <td>
                                    <div class="caregiver-name"><?= htmlspecialchars($req['caregiver']) ?></div>
                                    <div class="caregiver-phone"><?= htmlspecialchars($req['caregiver_contact']) ?></div>
                                </td>
                                <td><span class="datetime-text"><?= htmlspecialchars($req['datetime']) ?></span></td>
                                <td><?= htmlspecialchars($req['condition']) ?></td>
                                <td>
                                    <button class="btn-treatment"
                                            onclick="openTreatmentModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['patient_name'])) ?>')">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                        Add Treatment Plan
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ── COMPLETED TAB ────────────────────────────────────── -->
                <div class="table-wrap" id="tab-completed"
                     style="<?= $activeTab !== 'completed' ? 'display:none' : '' ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Caregiver &amp; Contact</th>
                                <th>Request Date &amp; Time</th>
                                <th>Condition Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($completedRequests)): ?>
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <p>No completed consultation requests.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($completedRequests as $req): ?>
                            <tr>
                                <td><span class="patient-name"><?= htmlspecialchars($req['patient_name']) ?></span></td>
                                <td>
                                    <div class="caregiver-name"><?= htmlspecialchars($req['caregiver']) ?></div>
                                    <div class="caregiver-phone"><?= htmlspecialchars($req['caregiver_contact']) ?></div>
                                </td>
                                <td><span class="datetime-text"><?= htmlspecialchars($req['datetime']) ?></span></td>
                                <td><?= htmlspecialchars($req['condition']) ?></td>
                                <td>
                                    <div class="completed-badge">COMPLETED</div>
                                    <div class="completed-date">Completed: <?= htmlspecialchars($req['completed_date']) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ── DECLINED TAB ─────────────────────────────────────── -->
                <div class="table-wrap" id="tab-declined"
                     style="<?= $activeTab !== 'declined' ? 'display:none' : '' ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Caregiver &amp; Contact</th>
                                <th>Request Date &amp; Time</th>
                                <th>Condition Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($declinedRequests)): ?>
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <p>No declined consultation requests.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($declinedRequests as $req): ?>
                            <tr>
                                <td><span class="patient-name"><?= htmlspecialchars($req['patient_name']) ?></span></td>
                                <td>
                                    <div class="caregiver-name"><?= htmlspecialchars($req['caregiver']) ?></div>
                                    <div class="caregiver-phone"><?= htmlspecialchars($req['caregiver_contact']) ?></div>
                                </td>
                                <td><span class="datetime-text"><?= htmlspecialchars($req['datetime']) ?></span></td>
                                <td><?= htmlspecialchars($req['condition']) ?></td>
                                <td>
                                    <div class="declined-badge">DECLINED</div>
                                    <div class="declined-reason"><?= htmlspecialchars($req['reason']) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div><!-- .tabs-card -->

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
            <div class="detail-label">Contact Number</div>
            <div class="detail-value" id="viewCaregiverContact"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Request Date &amp; Time</div>
            <div class="detail-value" id="viewDatetime"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Condition Summary</div>
            <div class="detail-value" id="viewCondition"></div>
        </div>
        <div class="modal-actions">
            <button class="btn-close-detail" onclick="closeModal('modalView')">Close</button>
        </div>
    </div>
</div>

<!-- Add Treatment Plan Modal -->
<div class="modal-overlay" id="modalTreatment">
    <div class="modal" style="max-width:520px;">
        <button class="modal-close" onclick="closeModal('modalTreatment')">&times;</button>
        <h3>Add Treatment Plan</h3>
        <p class="modal-subtitle">Create a treatment plan for <strong id="treatmentPatientName"></strong></p>

        <div class="form-group">
            <label class="form-label">Diagnosis <span class="req">*</span></label>
            <input type="text" class="form-control" placeholder="Enter diagnosis..." />
        </div>
        <div class="form-group">
            <label class="form-label">Treatment Type <span class="req">*</span></label>
            <select class="form-control">
                <option value="" disabled selected>select treatment type</option>
                <option>Physical Therapy</option>
                <option>Occupational Therapy</option>
                <option>Speech Therapy</option>
                <option>Home Care</option>
                <option>Medication Management</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Medications</label>
            <input type="text" class="form-control" placeholder="List prescribed medications and dosages..." />
        </div>
        <div class="form-group">
            <label class="form-label">Treatment Instructions <span class="req">*</span></label>
            <textarea class="form-control" placeholder="Enter detailed treatment instructions..."></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Start Date <span class="req">*</span></label>
                <input type="date" class="form-control" placeholder="mm/dd/yyyy" />
            </div>
            <div class="form-group">
                <label class="form-label">Duration (weeks)</label>
                <input type="number" class="form-control" placeholder="Enter duration..." min="1" />
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Follow-up Schedule</label>
            <select class="form-control">
                <option value="" disabled selected>select follow up frequency...</option>
                <option>Weekly</option>
                <option>Bi-weekly</option>
                <option>Monthly</option>
                <option>As needed</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Additional Notes</label>
            <textarea class="form-control" placeholder="Add any additional notes or recommendations..."></textarea>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalTreatment')">Cancel</button>
            <button class="btn-save-plan" onclick="saveTreatmentPlan()">Save Treatment Plan</button>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- JAVASCRIPT                                                       -->
<!-- ================================================================ -->
<script>
    let currentRequestId = null;

    function switchTab(tab) {
        ['pending','accepted','completed','declined'].forEach(t => {
            document.getElementById('tab-' + t).style.display = (t === tab) ? '' : 'none';
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('onclick').includes("'" + tab + "'"));
        });
    }

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

    function openViewModal(id, patientName, caregiver, caregiverContact, datetime, condition) {
        document.getElementById('viewPatientName').textContent      = patientName;
        document.getElementById('viewCaregiver').textContent        = caregiver;
        document.getElementById('viewCaregiverContact').textContent = caregiverContact;
        document.getElementById('viewDatetime').textContent         = datetime;
        document.getElementById('viewCondition').textContent        = condition;
        document.getElementById('modalView').classList.add('open');
    }

    function openTreatmentModal(id, patientName) {
        currentRequestId = id;
        document.getElementById('treatmentPatientName').textContent = patientName;
        document.getElementById('modalTreatment').classList.add('open');
    }
    function saveTreatmentPlan() {
        alert('Treatment plan saved for request ID: ' + currentRequestId + '\n(TODO: connect to backend)');
        closeModal('modalTreatment');
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