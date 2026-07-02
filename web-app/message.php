<?php
// ============================================================
//  AbleCare – Healthcare Provider Portal: Messages
// ============================================================
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'healthcare_provider') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
$db          = get_db();
$myUserId    = (int) $_SESSION['user_id'];

// ── Handle AJAX send ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['msg_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $receiverId  = (int) ($_POST['receiver_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');

    if (!$receiverId || $messageText === '') {
        echo json_encode(['ok' => false, 'error' => 'Missing receiver or message.']);
        exit;
    }

    // Verify receiver exists
    $chk = $db->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
    $chk->bind_param('i', $receiverId);
    $chk->execute();
    if (!$chk->get_result()->fetch_assoc()) {
        echo json_encode(['ok' => false, 'error' => 'Recipient not found.']);
        $chk->close();
        exit;
    }
    $chk->close();

    $ins = $db->prepare(
        'INSERT INTO messages (sender_id, receiver_id, message_text, is_read, sent_at)
         VALUES (?, ?, ?, 0, NOW())'
    );
    $ins->bind_param('iis', $myUserId, $receiverId, $messageText);
    if ($ins->execute()) {
        echo json_encode(['ok' => true, 'sent_at' => date('g:i A')]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Database error.']);
    }
    $ins->close();
    exit;
}

// ── Mark messages as read when opening a conversation ────────────────────────
$activeUserId = (int) ($_GET['user_id'] ?? 0);
if ($activeUserId > 0) {
    $markRead = $db->prepare(
        'UPDATE messages SET is_read = 1
         WHERE sender_id = ? AND receiver_id = ? AND is_read = 0'
    );
    $markRead->bind_param('ii', $activeUserId, $myUserId);
    $markRead->execute();
    $markRead->close();
}

// ── Load inbox (most-recent message per contact) ──────────────────────────────
$inboxStmt = $db->prepare(
    "SELECT
        other_id,
        m.message_text, m.sent_at, m.sender_id,
        u.first_name, u.last_name, u.role,
        (SELECT COUNT(*) FROM messages
         WHERE sender_id = other_id AND receiver_id = ? AND is_read = 0) AS unread_count
     FROM (
        SELECT
            CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_id,
            MAX(id) AS last_message_id
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY other_id
     ) latest
     JOIN messages m ON m.id = latest.last_message_id
     JOIN users u ON u.id = latest.other_id
     ORDER BY m.sent_at DESC"
);
$inboxStmt->bind_param('iiii', $myUserId, $myUserId, $myUserId, $myUserId);
$inboxStmt->execute();
$inboxRows = $inboxStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inboxStmt->close();

// If no user_id in URL, default to the first conversation
if ($activeUserId === 0 && !empty($inboxRows)) {
    $activeUserId = (int) $inboxRows[0]['other_id'];
}

// Map inbox rows to template shape
$conversations = array_map(function ($row) use ($activeUserId, $myUserId) {
    $isActive   = (int) $row['other_id'] === $activeUserId;
    $lastMsg    = $row['message_text'];
    $preview    = mb_strlen($lastMsg) > 50 ? mb_substr($lastMsg, 0, 47) . '...' : $lastMsg;
    $fromMe     = (int) $row['sender_id'] === $myUserId;
    $timeStr    = date('g:i A', strtotime($row['sent_at']));
    return [
        'id'      => (int) $row['other_id'],
        'avatar'  => '',
        'name'    => trim($row['first_name'] . ' ' . $row['last_name']),
        'role'    => ucfirst($row['role']),
        'time'    => $timeStr,
        'preview' => ($fromMe ? 'You: ' : '') . $preview,
        'unread'  => (int) $row['unread_count'],
        'online'  => false,
        'active'  => $isActive,
    ];
}, $inboxRows);

// ── Active conversation details ───────────────────────────────────────────────
$activeConversation = null;
if ($activeUserId > 0) {
    foreach ($conversations as $conv) {
        if ($conv['id'] === $activeUserId) {
            $activeConversation = $conv;
            break;
        }
    }
    // If the user_id is valid but not yet in our inbox (edge case), look them up
    if ($activeConversation === null) {
        $uStmt = $db->prepare('SELECT id, first_name, last_name, role FROM users WHERE id = ? LIMIT 1');
        $uStmt->bind_param('i', $activeUserId);
        $uStmt->execute();
        $uRow = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();
        if ($uRow) {
            $activeConversation = [
                'id'     => (int) $uRow['id'],
                'avatar' => '',
                'name'   => trim($uRow['first_name'] . ' ' . $uRow['last_name']),
                'role'   => ucfirst($uRow['role']),
                'online' => false,
            ];
        }
    }
}

// ── Load messages for the active conversation ─────────────────────────────────
$messages = [];
if ($activeUserId > 0) {
    $msgStmt = $db->prepare(
        'SELECT sender_id, message_text, sent_at
         FROM messages
         WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
         ORDER BY sent_at ASC'
    );
    $msgStmt->bind_param('iiii', $myUserId, $activeUserId, $activeUserId, $myUserId);
    $msgStmt->execute();
    $msgRows = $msgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $msgStmt->close();

    $messages = array_map(function ($row) use ($myUserId) {
        return [
            'sender'  => (int) $row['sender_id'] === $myUserId ? 'provider' : 'caregiver',
            'content' => $row['message_text'],
            'time'    => date('g:i A', strtotime($row['sent_at'])),
        ];
    }, $msgRows);
}

$provider = [
    'name'     => $_SESSION['full_name'] ?? 'Healthcare Provider',
    'role'     => 'Healthcare Provider',
    'hospital' => '',
    'avatar'   => '',
];

// ── Resolve hpId + fetch caregivers for "New Conversation" modal ──────────────
$hpStmt = $db->prepare('SELECT id FROM healthcare_providers WHERE user_id = ? LIMIT 1');
$hpStmt->bind_param('i', $myUserId);
$hpStmt->execute();
$hpRow2 = $hpStmt->get_result()->fetch_assoc();
$hpStmt->close();
$hpId = $hpRow2 ? (int) $hpRow2['id'] : 0;

$cgStmt = $db->prepare(
    'SELECT DISTINCT u.id AS user_id, u.first_name, u.last_name
     FROM consultations c
     JOIN caregivers cg ON cg.id = c.caregiver_id
     JOIN users u ON u.id = cg.user_id
     WHERE c.healthcare_provider_id = ? AND c.status = "accepted"
     ORDER BY u.first_name ASC'
);
$cgStmt->bind_param('i', $hpId);
$cgStmt->execute();
$assignedCaregivers = $cgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cgStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AbleCare — Messages</title>
    <link rel="stylesheet" href="css/message.css">
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
            <a href="consultation_requests.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg></span>
                Consultation Requests
            </a>
            <a href="therapy_schedule.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></span>
                Therapy Schedules
            </a>
            <a href="message.php" class="nav-item active">
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
                <div class="page-title">
                    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    Messages
                </div>
                <div class="breadcrumb">
                    <a href="dashboard_provider.php">Dashboard</a>
                    <span>›</span>
                    Messages
                </div>
            </div>

            <!-- Messages Layout -->
            <div class="messages-layout">

                <!-- Conversations Panel -->
                <div class="conversations-panel">
                    <div class="conversations-header">
                        <div class="conversations-title">Conversations</div>
                        <button class="btn-new-conv" onclick="openNewConvModal()" title="New Conversation">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                            New
                        </button>
                    </div>
                    <div class="conversations-list">
                        <?php if (empty($conversations)): ?>
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            <p>No conversations yet. Once you approve a consultation request, a conversation will appear here.</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                        <a href="?user_id=<?= $conv['id'] ?>" class="conv-item <?= $conv['active'] ? 'active' : '' ?>">
                            <div class="conv-avatar-wrap">
                                <img src="<?= htmlspecialchars($conv['avatar']) ?>" alt="<?= htmlspecialchars($conv['name']) ?>" class="conv-avatar" />
                                <?php if ($conv['online']): ?>
                                <div class="online-dot"></div>
                                <?php endif; ?>
                            </div>
                            <div class="conv-body">
                                <div class="conv-top">
                                    <span class="conv-name"><?= htmlspecialchars($conv['name']) ?></span>
                                    <span class="conv-time"><?= htmlspecialchars($conv['time']) ?></span>
                                </div>
                                <div class="conv-role-tag"><?= htmlspecialchars($conv['role']) ?></div>
                                <div class="conv-preview-row">
                                    <span class="conv-preview"><?= htmlspecialchars($conv['preview']) ?></span>
                                    <?php if ($conv['unread'] > 0): ?>
                                    <span class="unread-badge"><?= $conv['unread'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">

                    <?php if ($activeConversation === null): ?>
                    <div class="empty-state" style="flex:1;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <p>Select a conversation to start messaging.</p>
                    </div>
                    <?php else: ?>
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="chat-header-avatar-wrap">
                            <img src="<?= htmlspecialchars($activeConversation['avatar']) ?>" alt="<?= htmlspecialchars($activeConversation['name']) ?>" class="chat-header-avatar" />
                            <?php if ($activeConversation['online']): ?>
                            <div class="chat-header-online-dot"></div>
                            <?php endif; ?>
                        </div>
                        <div class="chat-header-info">
                            <div class="chat-header-name"><?= htmlspecialchars($activeConversation['name']) ?></div>
                            <div class="chat-header-status">
                                <span class="chat-role-tag"><?= htmlspecialchars($activeConversation['role']) ?></span>
                                <?php if ($activeConversation['online']): ?>
                                <span class="online-indicator"></span>
                                <span class="online-text">Online</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="messages-area" id="messagesArea">
                        <?php if (empty($messages)): ?>
                        <div class="empty-state">
                            <p>No messages yet.</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="message-row <?= $msg['sender'] ?>">
                            <div class="message-wrap">
                                <div class="bubble <?= $msg['sender'] ?>">
                                    <?= htmlspecialchars($msg['content']) ?>
                                </div>
                                <div class="message-time"><?= htmlspecialchars($msg['time']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Input Area -->
                    <div class="chat-input-area">
                        <input
                            type="text"
                            class="chat-input"
                            id="messageInput"
                            placeholder="Type your message..."
                            onkeydown="if(event.key==='Enter') sendMessage()"
                        />
                        <button class="btn-send" onclick="sendMessage()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            Send
                        </button>
                    </div>
                    <?php endif; ?>

                </div><!-- .chat-panel -->

            </div><!-- .messages-layout -->

        </div><!-- .content -->
    </div><!-- .main -->
</div><!-- .layout -->

<!-- ================================================================ -->
<!-- NEW CONVERSATION MODAL                                           -->
<!-- ================================================================ -->
<div class="modal-overlay" id="modalNewConv">
    <div class="modal" style="max-width:460px;">
        <button class="modal-close" onclick="closeNewConvModal()">&times;</button>
        <h3>New Conversation</h3>
        <p class="modal-subtitle">Start a conversation with one of your assigned patients' caregivers.</p>

        <?php if (empty($assignedCaregivers)): ?>
        <div style="padding:20px 0;text-align:center;color:#6b7280;font-size:14px;">
            No assigned patients yet. Approve a consultation request first.
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeNewConvModal()">Close</button>
        </div>
        <?php else: ?>
        <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label" style="font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block;">
                Caregiver <span style="color:#ef5350;">*</span>
            </label>
            <select id="ncCaregiver" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;outline:none;">
                <option value="" disabled selected>Select caregiver…</option>
                <?php foreach ($assignedCaregivers as $cg): ?>
                <option value="<?= (int) $cg['user_id'] ?>">
                    <?= htmlspecialchars($cg['first_name'] . ' ' . $cg['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:6px;">
            <label class="form-label" style="font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block;">
                First Message <span style="color:#ef5350;">*</span>
            </label>
            <textarea id="ncMessage"
                      style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;resize:vertical;min-height:90px;box-sizing:border-box;outline:none;"
                      placeholder="Type your opening message…"></textarea>
        </div>
        <p id="ncError" style="color:#ef5350;font-size:13px;margin:6px 0 0;min-height:18px;"></p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeNewConvModal()">Cancel</button>
            <button class="btn-primary" id="ncSendBtn" onclick="startNewConversation()">
                <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Start Conversation
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ── New Conversation button ── */
.btn-new-conv {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #26a69a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s;
}
.btn-new-conv:hover { background: #1e8e83; }

/* ── Modal overlay (matches existing provider dashboard modals) ── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal {
    background: #fff;
    border-radius: 14px;
    padding: 28px 28px 24px;
    width: 90%;
    max-width: 480px;
    position: relative;
    box-shadow: 0 20px 48px rgba(0,0,0,0.18);
    max-height: 90vh;
    overflow-y: auto;
}
.modal-close {
    position: absolute;
    top: 14px;
    right: 16px;
    background: none;
    border: none;
    font-size: 22px;
    color: #9ca3af;
    cursor: pointer;
    line-height: 1;
}
.modal-close:hover { color: #374151; }
.modal h3 { margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #111827; }
.modal-subtitle { font-size: 13px; color: #6b7280; margin: 0 0 18px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
.btn-cancel {
    padding: 8px 18px;
    background: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.btn-cancel:hover { background: #e5e7eb; }
.btn-primary {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 18px;
    background: #26a69a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.btn-primary:hover { background: #1e8e83; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

/* Keep conversations-header flex with space-between */
.conversations-header {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
}
</style>

<!-- ================================================================ -->
<!-- JAVASCRIPT — Message Send                                        -->
<!-- ================================================================ -->
<script>
    const ACTIVE_USER_ID = <?= (int) $activeUserId ?>;
    const area = document.getElementById('messagesArea');
    if (area) area.scrollTop = area.scrollHeight;

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        if (!text || !ACTIVE_USER_ID) return;

        const fd = new FormData();
        fd.append('msg_action', 'send');
        fd.append('receiver_id', ACTIVE_USER_ID);
        fd.append('message_text', text);

        input.value = '';
        // Optimistically append the bubble
        const now  = new Date();
        const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        appendMessage(text, time);

        fetch('message.php', { method: 'POST', body: fd })
            .catch(() => { /* message already shown; ignore network errors silently */ });
    }

    function appendMessage(text, time) {
        const row = document.createElement('div');
        row.className = 'message-row provider';
        row.innerHTML = `
            <div class="message-wrap">
                <div class="bubble provider">${escapeHtml(text)}</div>
                <div class="message-time">${time}</div>
            </div>`;
        area.appendChild(row);
        area.scrollTop = area.scrollHeight;
    }

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── New Conversation Modal ────────────────────────────────────────────────────
    function openNewConvModal() {
        const sel = document.getElementById('ncCaregiver');
        const msg = document.getElementById('ncMessage');
        if (sel) sel.value = '';
        if (msg) msg.value = '';
        document.getElementById('ncError').textContent = '';
        document.getElementById('modalNewConv').classList.add('open');
    }

    function closeNewConvModal() {
        document.getElementById('modalNewConv').classList.remove('open');
    }

    function startNewConversation() {
        const receiverId = document.getElementById('ncCaregiver').value;
        const text       = document.getElementById('ncMessage').value.trim();
        const errEl      = document.getElementById('ncError');
        const sendBtn    = document.getElementById('ncSendBtn');

        if (!receiverId) { errEl.textContent = 'Please select a caregiver.'; return; }
        if (!text)       { errEl.textContent = 'Please enter a message.'; return; }
        errEl.textContent = '';

        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending…';

        const fd = new FormData();
        fd.append('msg_action', 'send');
        fd.append('receiver_id', receiverId);
        fd.append('message_text', text);

        fetch('message.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(r => {
                if (r.ok) {
                    window.location.href = 'message.php?user_id=' + receiverId;
                } else {
                    errEl.textContent = r.error || 'Failed to send message.';
                    sendBtn.disabled = false;
                    sendBtn.textContent = 'Start Conversation';
                }
            })
            .catch(() => {
                errEl.textContent = 'Network error. Please try again.';
                sendBtn.disabled = false;
                sendBtn.textContent = 'Start Conversation';
            });
    }

    // Close modal on overlay click or Escape
    document.getElementById('modalNewConv').addEventListener('click', function(e) {
        if (e.target === this) closeNewConvModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNewConvModal();
    });
</script>
</body>
</html>