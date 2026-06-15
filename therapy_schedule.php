<?php
/**
 * AbleCare - Healthcare Provider Portal
 * Therapy Schedules Page
 *
 * TODO (Database Integration):
 * - Replace dummy $sessions array with actual DB queries (PDO/MySQLi).
 * - Connect session/auth to verify logged-in provider.
 * - Action handlers (view/complete/edit/cancel) should POST to API endpoints
 *   or the same page with action logic at the top.
 * - "Add Therapy Session" should INSERT into therapy_sessions table.
 */

// -----------------------------------------------------------------------
// DUMMY DATA — replace with DB queries when ready
// -----------------------------------------------------------------------

$provider = [
    'name'     => 'Dr. Jose Reyes',
    'role'     => 'Healthcare Provider',
    'hospital' => 'Nasugbu General Hospital',
    'avatar'   => 'https://ui-avatars.com/api/?name=Jose+Reyes&background=4db6ac&color=fff&size=40',
];

// TODO: SELECT * FROM therapy_sessions WHERE provider_id = ? ORDER BY session_date DESC
$sessions = [
    [
        'id'             => 1,
        'patient_name'   => 'Maria Lopez',
        'therapy_type'   => 'Physical Therapy',
        'datetime'       => 'April 1, 2026 – 9:00 AM',
        'date_val'       => '04/01/26',
        'time_val'       => '09:00 AM',
        'progress_done'  => 8,
        'progress_total' => 12,
        'status'         => 'scheduled',
    ],
    [
        'id'             => 2,
        'patient_name'   => 'Juan dela Cruz',
        'therapy_type'   => 'Physical Therapy',
        'datetime'       => 'April 1, 2026 – 10:30 AM',
        'date_val'       => '04/01/26',
        'time_val'       => '10:30 AM',
        'progress_done'  => 10,
        'progress_total' => 12,
        'status'         => 'scheduled',
    ],
    [
        'id'             => 3,
        'patient_name'   => 'Rosa Martinez',
        'therapy_type'   => 'Occupational Therapy',
        'datetime'       => 'March 31, 2026 – 2:00 PM',
        'date_val'       => '03/31/26',
        'time_val'       => '02:00 PM',
        'progress_done'  => 12,
        'progress_total' => 12,
        'status'         => 'completed',
    ],
    [
        'id'             => 4,
        'patient_name'   => 'Carlos Reyes',
        'therapy_type'   => 'Speech Therapy',
        'datetime'       => 'March 31, 2026 – 3:30 PM',
        'date_val'       => '03/31/26',
        'time_val'       => '03:30 PM',
        'progress_done'  => 5,
        'progress_total' => 10,
        'status'         => 'completed',
    ],
    [
        'id'             => 5,
        'patient_name'   => 'Linda Garcia',
        'therapy_type'   => 'Physical Therapy',
        'datetime'       => 'March 30, 2026 – 11:00 AM',
        'date_val'       => '03/30/26',
        'time_val'       => '11:00 AM',
        'progress_done'  => 3,
        'progress_total' => 10,
        'status'         => 'cancelled',
    ],
];

// Status badge config
$statusConfig = [
    'scheduled'  => ['label' => 'Scheduled',  'class' => 'badge-scheduled'],
    'completed'  => ['label' => 'Completed',  'class' => 'badge-completed'],
    'cancelled'  => ['label' => 'Cancelled',  'class' => 'badge-cancelled'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AbleCare — Therapy Schedules</title>
    <link rel="stylesheet" href="css/therapy_sched.css">
</head>
<body>
<div class="layout">

    <!-- ================================================================ -->
    <!-- SIDEBAR (with logo only, no green background)                    -->
    <!-- ================================================================ -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:50px;height:auto; border-radius:10px;">
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
            <a href="consultation_requests.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>
                Consultation Requests
            </a>
            <a href="therapy_schedule.php" class="nav-item active">
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
                <div>
                    <div class="page-title-row">
                        <div class="page-title-icon">
                            <svg viewBox="0 0 24 24" fill="#26a69a"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                        </div>
                        <h1 class="page-title">Therapy Schedules</h1>
                    </div>
                    <div class="breadcrumb">
                        <a href="dashboard_provider.php">Dashboard</a> &rsaquo; <span>Therapy Schedules</span>
                    </div>
                </div>
                <div class="header-controls">
                    <div class="view-toggle">
                        <button class="view-btn" title="Calendar View">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                        </button>
                        <button class="view-btn active" title="List View">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                        </button>
                    </div>
                    <button class="btn-add-session" onclick="openAddModal()">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Add Therapy Session
                    </button>
                </div>
            </div>

            <!-- Sessions Table -->
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Therapy Type</th>
                            <th>Scheduled Date &amp; Time</th>
                            <th>Session Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sessions as $s):
                        $pct = $s['progress_total'] > 0 ? round(($s['progress_done'] / $s['progress_total']) * 100) : 0;
                        $fillClass = $pct >= 100 ? 'full' : ($pct >= 50 ? '' : ($pct >= 30 ? 'medium' : 'low'));
                        $badge = $statusConfig[$s['status']] ?? ['label' => ucfirst($s['status']), 'class' => ''];
                    ?>
                        <tr>
                            <td><span class="patient-name"><?= htmlspecialchars($s['patient_name']) ?></span></td>
                            <td><span class="therapy-link"><?= htmlspecialchars($s['therapy_type']) ?></span></td>
                            <td><?= htmlspecialchars($s['datetime']) ?></td>
                            <td>
                                <div class="progress-wrap">
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill <?= $fillClass ?>"
                                             style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="progress-label"><?= $s['progress_done'] ?>/<?= $s['progress_total'] ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?= $badge['class'] ?>">
                                    <?= $badge['label'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <button class="action-btn" title="View Details"
                                            onclick="openViewModal(
                                                <?= $s['id'] ?>,
                                                '<?= htmlspecialchars(addslashes($s['patient_name'])) ?>',
                                                '<?= htmlspecialchars(addslashes($s['therapy_type'])) ?>',
                                                '<?= htmlspecialchars(addslashes($s['datetime'])) ?>',
                                                <?= $s['progress_done'] ?>,
                                                <?= $s['progress_total'] ?>,
                                                '<?= $s['status'] ?>'
                                            )">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#5c9bd6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Mark as Complete"
                                            onclick="openCompleteModal(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['patient_name'])) ?>')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#26a69a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="9 12 11 14 15 10"/>
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Edit Session"
                                            onclick="openEditModal(
                                                <?= $s['id'] ?>,
                                                '<?= htmlspecialchars(addslashes($s['patient_name'])) ?>',
                                                '<?= htmlspecialchars(addslashes($s['therapy_type'])) ?>',
                                                '<?= htmlspecialchars(addslashes($s['date_val'])) ?>',
                                                '<?= htmlspecialchars(addslashes($s['time_val'])) ?>'
                                            )">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Cancel Session"
                                            onclick="openCancelModal(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['patient_name'])) ?>')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#ef5350" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- .content -->
    </div><!-- .main -->
</div><!-- .layout -->

<!-- ================================================================ -->
<!-- MODALS                                                           -->
<!-- ================================================================ -->

<!-- Add New Therapy Session Modal -->
<div class="modal-overlay" id="modalAdd">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalAdd')">&times;</button>
        <h3>Add New Therapy Session</h3>
        <p class="modal-subtitle">Schedule a new therapy session for a patient</p>

        <div class="modal-info-banner">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
            Fill in all required fields to schedule a new therapy session
        </div>

        <div class="form-group">
            <label class="form-label">Patient <span class="req">*</span></label>
            <select class="form-control" id="addPatient">
                <option value="" disabled selected>Select Patient</option>
                <option>Maria Lopez</option>
                <option>Juan dela Cruz</option>
                <option>Rosa Martinez</option>
                <option>Carlos Reyes</option>
                <option>Linda Garcia</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Therapy Type <span class="req">*</span></label>
            <input type="text" class="form-control" id="addTherapyType" placeholder="Physical Therapy" />
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Date <span class="req">*</span></label>
                <input type="date" class="form-control" id="addDate" placeholder="mm/dd/yyyy" />
            </div>
            <div class="form-group">
                <label class="form-label">Time <span class="req">*</span></label>
                <input type="time" class="form-control" id="addTime" placeholder="--:-- --" />
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Session Notes</label>
            <textarea class="form-control" id="addNotes"
                      placeholder="Add any session notes, goals, or special instructions for this therapy session..."></textarea>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalAdd')">Cancel</button>
            <button class="btn-primary" onclick="confirmAddSession()">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Schedule Session
            </button>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal-overlay" id="modalView">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalView')">&times;</button>
        <h3>Therapy Session Details</h3>
        <div style="margin-bottom: 20px;"></div>

        <div class="detail-field">
            <div class="detail-label">Patient</div>
            <div class="detail-value" id="viewPatient"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Therapy Type</div>
            <div class="detail-value teal" id="viewTherapyType"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Scheduled Date &amp; Time</div>
            <div class="detail-value" id="viewDatetime"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Session Progress</div>
            <div class="detail-progress-wrap">
                <div class="detail-progress-bg">
                    <div class="detail-progress-fill" id="viewProgressFill" style="width:0%"></div>
                </div>
                <span class="detail-progress-label" id="viewProgressLabel"></span>
            </div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Status</div>
            <span class="detail-status-pill status-badge" id="viewStatusBadge"></span>
        </div>

        <div class="modal-actions">
            <button class="btn-close" onclick="closeModal('modalView')">Close</button>
        </div>
    </div>
</div>

<!-- Mark Session as Complete Modal -->
<div class="modal-overlay" id="modalComplete">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalComplete')">&times;</button>
        <h3>Mark Session as Complete</h3>
        <p class="modal-subtitle">Complete the therapy session for <strong id="completePatientName"></strong></p>

        <div class="modal-info-banner">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            This will mark the session as completed and update the patient's progress.
        </div>

        <div class="form-group">
            <label class="form-label">Session Completion Notes</label>
            <textarea class="form-control" id="completeNotes"
                      placeholder="Add notes about what was accomplished during this session..."></textarea>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalComplete')">Cancel</button>
            <button class="btn-primary" onclick="confirmComplete()">Mark Complete</button>
        </div>
    </div>
</div>

<!-- Edit Therapy Session Modal -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalEdit')">&times;</button>
        <h3>Edit Therapy Session</h3>
        <p class="modal-subtitle">Update the therapy session details</p>

        <div class="form-group">
            <label class="form-label">Patient <span class="req">*</span></label>
            <input type="text" class="form-control" id="editPatient" readonly />
        </div>
        <div class="form-group">
            <label class="form-label">Therapy Type <span class="req">*</span></label>
            <input type="text" class="form-control" id="editTherapyType" />
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Date <span class="req">*</span></label>
                <input type="text" class="form-control" id="editDate" placeholder="mm/dd/yy" />
            </div>
            <div class="form-group">
                <label class="form-label">Time <span class="req">*</span></label>
                <input type="text" class="form-control" id="editTime" placeholder="09:00 AM" />
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Session Notes</label>
            <textarea class="form-control" id="editNotes"
                      placeholder="Add any session notes or special instructions..."></textarea>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalEdit')">Cancel</button>
            <button class="btn-primary" onclick="confirmEdit()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Cancel Therapy Session Modal -->
<div class="modal-overlay" id="modalCancel">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalCancel')">&times;</button>
        <h3>Cancel Therapy Session</h3>
        <p style="font-size:14px; color:#6b7280; margin-bottom:16px;">
            Are you sure you want to cancel the therapy session for
            <strong id="cancelPatientName" style="color:#26a69a;"></strong>?
        </p>

        <div class="modal-warn-banner">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
            <span>This action will mark the session as cancelled. The patient and caregiver will be notified.</span>
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalCancel')">Keep Session</button>
            <button class="btn-danger" onclick="confirmCancel()">Confirm Cancel</button>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- JAVASCRIPT                                                       -->
<!-- ================================================================ -->
<script>
    let currentSessionId = null;

    const badgeClass = {
        scheduled:  'badge-scheduled',
        completed:  'badge-completed',
        cancelled:  'badge-cancelled',
    };
    const badgeLabel = {
        scheduled: 'Scheduled',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };

    function openAddModal() {
        document.getElementById('modalAdd').classList.add('open');
    }
    function confirmAddSession() {
        alert('Session scheduled!\n(TODO: connect to backend)');
        closeModal('modalAdd');
    }

    function openViewModal(id, patient, therapyType, datetime, done, total, status) {
        const pct = total > 0 ? Math.round((done / total) * 100) : 0;
        document.getElementById('viewPatient').textContent      = patient;
        document.getElementById('viewTherapyType').textContent  = therapyType;
        document.getElementById('viewDatetime').textContent     = datetime;
        document.getElementById('viewProgressFill').style.width = pct + '%';
        document.getElementById('viewProgressLabel').textContent = done + '/' + total;
        const badge = document.getElementById('viewStatusBadge');
        badge.textContent  = badgeLabel[status] || status;
        badge.className    = 'detail-status-pill status-badge ' + (badgeClass[status] || '');
        document.getElementById('modalView').classList.add('open');
    }

    function openCompleteModal(id, patient) {
        currentSessionId = id;
        document.getElementById('completePatientName').textContent = patient;
        document.getElementById('completeNotes').value = '';
        document.getElementById('modalComplete').classList.add('open');
    }
    function confirmComplete() {
        const notes = document.getElementById('completeNotes').value;
        alert('Session ' + currentSessionId + ' marked complete!\n(TODO: connect to backend)');
        closeModal('modalComplete');
    }

    function openEditModal(id, patient, therapyType, date, time) {
        currentSessionId = id;
        document.getElementById('editPatient').value      = patient;
        document.getElementById('editTherapyType').value  = therapyType;
        document.getElementById('editDate').value          = date;
        document.getElementById('editTime').value          = time;
        document.getElementById('editNotes').value         = '';
        document.getElementById('modalEdit').classList.add('open');
    }
    function confirmEdit() {
        alert('Session ' + currentSessionId + ' updated!\n(TODO: connect to backend)');
        closeModal('modalEdit');
    }

    function openCancelModal(id, patient) {
        currentSessionId = id;
        document.getElementById('cancelPatientName').textContent = patient;
        document.getElementById('modalCancel').classList.add('open');
    }
    function confirmCancel() {
        alert('Session ' + currentSessionId + ' cancelled!\n(TODO: connect to backend)');
        closeModal('modalCancel');
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