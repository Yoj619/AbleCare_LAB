<?php
// ============================================================
//  AbleCare – Provider: Consultation Requests (full list)
// ============================================================
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'healthcare_provider') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
$db = get_db();

// ── Resolve provider ID ───────────────────────────────────────────────────────
$stmt = $db->prepare('SELECT hp.id, cl.name AS clinic_name
                      FROM healthcare_providers hp
                      LEFT JOIN clinics cl ON cl.id = hp.clinic_id
                      WHERE hp.user_id = ? LIMIT 1');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$hpRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$hpRow) { header('Location: provider_settings.php'); exit; }
$hpId = (int) $hpRow['id'];

// ── Handle Approve / Decline POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consult_action'])) {
    $consultId = (int) ($_POST['consult_id'] ?? 0);
    $action    = $_POST['consult_action'];
    if ($consultId > 0 && in_array($action, ['accept', 'decline'], true)) {
        if ($action === 'accept') {
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
    header('Location: consultation_requests.php?status=' . urlencode($_POST['filter_status'] ?? 'pending') . '&action=' . urlencode($action));
    exit;
}

// ── Filter ────────────────────────────────────────────────────────────────────
$filterStatus = $_GET['status'] ?? 'pending';
$allowed      = ['pending', 'accepted', 'declined', 'completed', 'all'];
if (!in_array($filterStatus, $allowed, true)) $filterStatus = 'pending';

$whereStatus = $filterStatus !== 'all'
    ? "AND c.status = '" . $db->real_escape_string($filterStatus) . "'"
    : '';

// ── Query ─────────────────────────────────────────────────────────────────────
$consultResult = $db->query("
    SELECT c.id, c.status, c.notes, c.created_at, c.updated_at,
           p.first_name AS pt_first, p.last_name AS pt_last,
           p.specific_condition, p.disability_category,
           uc.first_name AS cg_first, uc.last_name AS cg_last, uc.phone_number AS cg_phone,
           cg.address AS cg_address, cg.barangay AS cg_barangay
    FROM consultations c
    JOIN patients   p  ON p.id  = c.patient_id
    JOIN caregivers cg ON cg.id = c.caregiver_id
    JOIN users      uc ON uc.id = cg.user_id
    WHERE c.healthcare_provider_id = $hpId $whereStatus
    ORDER BY c.created_at DESC
");
$requests  = $consultResult ? $consultResult->fetch_all(MYSQLI_ASSOC) : [];
$actionMsg = $_GET['action'] ?? '';
$providerName = $_SESSION['full_name'] ?? 'Healthcare Provider';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AbleCare — Consultation Requests</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/provider_dashboard.css">
    <style>
        .filter-bar{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;}
        .filter-btn{padding:6px 16px;border-radius:20px;border:1.5px solid #dde8e7;background:#fff;color:#3aafa9;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;transition:background .15s;}
        .filter-btn:hover,.filter-btn.active{background:#3aafa9;color:#fff;border-color:#3aafa9;}
        .status-pill{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:capitalize;}
        .status-pending{background:#FFF9C4;color:#856404;}
        .status-accepted{background:#d1f0ee;color:#1a6b66;}
        .status-declined{background:#fde8e4;color:#9a2412;}
        .status-completed{background:#e0e7ff;color:#3730a3;}
        .btn-sm{padding:4px 12px;border-radius:6px;border:none;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;margin-left:3px;}
        .btn-sm-view{background:#eaf1fb;color:#2563eb;}
        .btn-sm-view:hover{background:#2563eb;color:#fff;}
        .btn-sm-approve{background:#d1f0ee;color:#1a6b66;}
        .btn-sm-approve:hover{background:#3aafa9;color:#fff;}
        .btn-sm-decline{background:#fde8e4;color:#9a2412;}
        .btn-sm-decline:hover{background:#ef5350;color:#fff;}
        .note-text{font-size:11px;color:#9db8b8;font-style:italic;margin-top:3px;}
        .page-title-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;}
    </style>
</head>
<body>
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:56px;height:56px;object-fit:contain;border-radius:10px;">
            <div class="brand-text">
                <span class="brand-name">AbleCare</span>
                <span class="brand-sub">Healthcare Provider</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard_provider.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>Dashboard
            </a>
            <a href="mypatients.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></span>My Patients
            </a>
            <a href="consultation_requests.php" class="nav-item active">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>Consultation Requests
            </a>
            <a href="therapy_schedule.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></span>Therapy Schedules
            </a>
            <a href="message.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg></span>Messages
            </a>
            <a href="provider_settings.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg></span>Account Settings
            </a>
        </nav>
        <div class="sidebar-logout">
            <a href="logout.php">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="main">
        <header class="topbar">
            <div>
                <div class="topbar-title">Consultation Requests</div>
                <div class="topbar-sub"><?= htmlspecialchars($hpRow['clinic_name'] ?? '') ?></div>
            </div>
            <div class="topbar-user">
                <div class="topbar-user-info">
                    <div class="topbar-user-name"><?= htmlspecialchars($providerName) ?></div>
                    <div class="topbar-user-role">Healthcare Provider</div>
                </div>
            </div>
        </header>

        <div class="content">
            <div class="card">
                <div class="card-inner">
                    <div class="page-title-row">
                        <div>
                            <div class="section-title">All Consultation Requests</div>
                            <div class="section-sub">Review and manage caregiver consultation requests</div>
                        </div>
                    </div>

                    <!-- Status filter -->
                    <div class="filter-bar">
                        <?php foreach (['pending'=>'Pending','accepted'=>'Approved','declined'=>'Declined','completed'=>'Completed','all'=>'All'] as $val => $lbl): ?>
                        <a href="?status=<?= $val ?>" class="filter-btn <?= $filterStatus === $val ? 'active' : '' ?>"><?= $lbl ?></a>
                        <?php endforeach; ?>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Caregiver</th>
                                <th>Condition</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="6">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <p>No <?= $filterStatus !== 'all' ? htmlspecialchars($filterStatus) . ' ' : '' ?>consultation requests found.</p>
                                </div>
                            </td></tr>
                        <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['pt_first'] . ' ' . $r['pt_last']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($r['cg_first'] . ' ' . $r['cg_last']) ?>
                                    <?php if ($r['cg_phone']): ?>
                                    <div style="font-size:11px;color:#9db8b8;"><?= htmlspecialchars($r['cg_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($r['specific_condition'] ?: ucfirst(str_replace('_', ' ', $r['disability_category'] ?? ''))) ?></td>
                                <td style="white-space:nowrap;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td><span class="status-pill status-<?= htmlspecialchars($r['status']) ?>"><?= ucfirst(htmlspecialchars($r['status'])) ?></span></td>
                                <td style="white-space:nowrap;">
                                    <button class="btn-sm btn-sm-view"
                                            onclick="openViewModal(<?= (int)$r['id'] ?>)">View</button>
                                    <?php if ($r['status'] === 'pending'): ?>
                                    <button class="btn-sm btn-sm-approve"
                                            onclick="openAcceptModal(<?= (int)$r['id'] ?>, '<?= htmlspecialchars(addslashes($r['pt_first'] . ' ' . $r['pt_last'])) ?>')">Approve</button>
                                    <button class="btn-sm btn-sm-decline"
                                            onclick="openDeclineModal(<?= (int)$r['id'] ?>, '<?= htmlspecialchars(addslashes($r['pt_first'] . ' ' . $r['pt_last'])) ?>')">Decline</button>
                                    <?php endif; ?>
                                    <?php if ($r['status'] === 'declined' && $r['notes']): ?>
                                    <div class="note-text">Note: <?= htmlspecialchars($r['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accept Modal -->
<div class="modal-overlay" id="modalAccept">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalAccept')">&times;</button>
        <h3>Approve Consultation Request</h3>
        <p>Approve the consultation request for <strong id="acceptPatientName"></strong>?</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalAccept')">Cancel</button>
            <button class="btn-confirm-accept" onclick="confirmAccept()">Confirm Approve</button>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal-overlay" id="modalDecline">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modalDecline')">&times;</button>
        <h3>Decline Consultation Request</h3>
        <p>Decline the request for <strong id="declinePatientName"></strong>?</p>
        <label class="decline-reason-label">Reason for Declining (Optional)</label>
        <textarea class="decline-textarea" id="declineReason" placeholder="Enter reason…"></textarea>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalDecline')">Cancel</button>
            <button class="btn-confirm-decline" onclick="confirmDecline()">Confirm Decline</button>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal-overlay" id="modalView">
    <div class="modal" style="max-width:520px;">
        <button class="modal-close" onclick="closeModal('modalView')">&times;</button>
        <h3>Consultation Details</h3>
        <div id="viewModalBody"></div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal('modalView')">Close</button>
            <button class="btn-confirm-accept" id="viewApproveBtn"
                    onclick="closeModal('modalView');openAcceptModal(viewCurrentId,viewCurrentPatient)">Approve</button>
            <button class="btn-confirm-decline" id="viewDeclineBtn"
                    onclick="closeModal('modalView');openDeclineModal(viewCurrentId,viewCurrentPatient)">Decline</button>
        </div>
    </div>
</div>

<!-- Hidden action form -->
<form id="consultActionForm" method="POST" action="consultation_requests.php" style="display:none;">
    <input type="hidden" name="consult_id"     id="hiddenConsultId">
    <input type="hidden" name="consult_action" id="hiddenAction">
    <input type="hidden" name="decline_reason" id="hiddenDeclineReason" value="">
    <input type="hidden" name="filter_status"  value="<?= htmlspecialchars($filterStatus) ?>">
</form>

<script>
var CONSULT_DATA = <?= json_encode(
    array_column(array_map(function($r) {
        return [
            'id'          => (int) $r['id'],
            'status'      => $r['status'],
            'patientName' => $r['pt_first'] . ' ' . $r['pt_last'],
            'condition'   => $r['specific_condition'] ?: ucfirst(str_replace('_', ' ', $r['disability_category'] ?? '')),
            'cgName'      => $r['cg_first'] . ' ' . $r['cg_last'],
            'cgPhone'     => $r['cg_phone'] ?? '',
            'cgAddress'   => trim(($r['cg_address'] ?? '') . ($r['cg_barangay'] ? ', Brgy. ' . $r['cg_barangay'] : '')),
            'date'        => date('M d, Y · g:i A', strtotime($r['created_at'])),
            'notes'       => $r['notes'] ?? '',
        ];
    }, $requests), null, 'id'),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?>;

<?php if ($actionMsg !== ''): ?>
(function(){
    var m = document.createElement('div');
    m.textContent = '<?= $actionMsg === 'accept' ? 'Request approved.' : 'Request declined.' ?>';
    m.style.cssText = 'position:fixed;top:16px;right:20px;z-index:9999;background:<?= $actionMsg === 'accept' ? '#26a69a' : '#ef5350' ?>;color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.18);';
    document.body.appendChild(m);
    setTimeout(function(){ m.remove(); }, 3500);
})();
<?php endif; ?>

var currentRequestId   = null;
var viewCurrentId      = null;
var viewCurrentPatient = '';

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
    document.getElementById('hiddenConsultId').value     = currentRequestId;
    document.getElementById('hiddenAction').value        = 'accept';
    document.getElementById('hiddenDeclineReason').value = '';
    document.getElementById('consultActionForm').submit();
}
function openDeclineModal(id, patientName) {
    currentRequestId = id;
    document.getElementById('declinePatientName').textContent = patientName;
    document.getElementById('declineReason').value = '';
    document.getElementById('modalDecline').classList.add('open');
}
function confirmDecline() {
    document.getElementById('hiddenConsultId').value     = currentRequestId;
    document.getElementById('hiddenAction').value        = 'decline';
    document.getElementById('hiddenDeclineReason').value = document.getElementById('declineReason').value;
    document.getElementById('consultActionForm').submit();
}
function openViewModal(id) {
    var d = CONSULT_DATA[id];
    if (!d) return;
    viewCurrentId      = id;
    viewCurrentPatient = d.patientName;

    document.getElementById('viewModalBody').innerHTML =
        '<div class="detail-field"><div class="detail-label">Patient</div><div class="detail-value">'          + ve(d.patientName) + '</div></div>' +
        '<div class="detail-field"><div class="detail-label">Condition / Disability</div><div class="detail-value">' + ve(d.condition)   + '</div></div>' +
        '<div class="detail-field"><div class="detail-label">Caregiver</div><div class="detail-value">'        + ve(d.cgName)      + '</div></div>' +
        '<div class="detail-field"><div class="detail-label">Caregiver Phone</div><div class="detail-value">'  + ve(d.cgPhone)     + '</div></div>' +
        '<div class="detail-field"><div class="detail-label">Caregiver Address</div><div class="detail-value">'+ ve(d.cgAddress)   + '</div></div>' +
        '<div class="detail-field"><div class="detail-label">Date Requested</div><div class="detail-value">'   + ve(d.date)        + '</div></div>' +
        (d.notes ? '<div class="detail-field"><div class="detail-label">Provider Note</div><div class="detail-value">' + ve(d.notes) + '</div></div>' : '');

    var isPending = d.status === 'pending';
    document.getElementById('viewApproveBtn').style.display = isPending ? '' : 'none';
    document.getElementById('viewDeclineBtn').style.display = isPending ? '' : 'none';
    document.getElementById('modalView').classList.add('open');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') ['modalAccept','modalDecline','modalView'].forEach(closeModal);
});
</script>
</body>
</html>
