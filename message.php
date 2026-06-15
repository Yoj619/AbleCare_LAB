<?php
/**
 * AbleCare - Healthcare Provider Portal
 * Messages Page
 *
 * TODO (Database Integration):
 * - Replace dummy $conversations and $messages arrays with actual DB queries.
 * - Connect session/auth to verify logged-in provider.
 * - Load conversations: SELECT * FROM conversations WHERE provider_id = ? ORDER BY last_message_at DESC
 * - Load messages: SELECT * FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC
 * - Send message: INSERT INTO messages (conversation_id, sender_id, content, sent_at) VALUES (...)
 */

// -----------------------------------------------------------------------
// DUMMY DATA — replace with DB queries when ready
// -----------------------------------------------------------------------

$provider = [
    'name'       => 'Dr. Jose Reyes',
    'role'       => 'Healthcare Provider',
    'hospital'   => 'Nasugbu General Hospital',
    'avatar'     => 'https://ui-avatars.com/api/?name=Jose+Reyes&background=4db6ac&color=fff&size=40',
];

$conversations = [
    [
        'id'            => 1,
        'name'          => 'Maria Santos',
        'role'          => 'Caregiver',
        'avatar'        => 'https://ui-avatars.com/api/?name=Maria+Santos&background=e0f2f1&color=26a69a&size=40',
        'time'          => '10:30 AM',
        'preview'       => 'Thank you for the treatme...',
        'unread'        => 2,
        'online'        => true,
        'active'        => true,
    ],
    [
        'id'            => 2,
        'name'          => 'Pedro Martinez',
        'role'          => 'Caregiver',
        'avatar'        => 'https://ui-avatars.com/api/?name=Pedro+Martinez&background=e3f2fd&color=1976d2&size=40',
        'time'          => 'Yesterday',
        'preview'       => 'When is the next therapy sessio...',
        'unread'        => 0,
        'online'        => false,
        'active'        => false,
    ],
    [
        'id'            => 3,
        'name'          => 'Ana Reyes',
        'role'          => 'Caregiver',
        'avatar'        => 'https://ui-avatars.com/api/?name=Ana+Reyes&background=fce4ec&color=e91e63&size=40',
        'time'          => '2 days ago',
        'preview'       => 'Carlos is showing good progress',
        'unread'        => 0,
        'online'        => false,
        'active'        => false,
    ],
];

$activeConversation = $conversations[0];

$messages = [
    [
        'id'        => 1,
        'sender'    => 'provider',
        'content'   => 'occurring? And what is his current blood pressure reading?',
        'time'      => '9:50 AM',
    ],
    [
        'id'        => 2,
        'sender'    => 'caregiver',
        'content'   => 'About 3 times a day. His blood pressure this morning was 150/90.',
        'time'      => '10:15 AM',
    ],
    [
        'id'        => 3,
        'sender'    => 'provider',
        'content'   => 'Thank you for monitoring this. I\'m updating his treatment plan to adjust the medication dosage. Please bring him in for a follow-up consultation next week.',
        'time'      => '10:28 AM',
    ],
    [
        'id'        => 4,
        'sender'    => 'caregiver',
        'content'   => 'Thank you for the treatment plan, Doctor.',
        'time'      => '10:30 AM',
    ],
];
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
                    </div>
                    <div class="conversations-list">
                        <?php foreach ($conversations as $conv): ?>
                        <a href="?conversation=<?= $conv['id'] ?>" class="conv-item <?= $conv['active'] ? 'active' : '' ?>">
                            <div class="conv-avatar-wrap">
                                <img src="<?= $conv['avatar'] ?>" alt="<?= htmlspecialchars($conv['name']) ?>" class="conv-avatar" />
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
                    </div>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">

                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="chat-header-avatar-wrap">
                            <img src="<?= $activeConversation['avatar'] ?>" alt="<?= htmlspecialchars($activeConversation['name']) ?>" class="chat-header-avatar" />
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

                </div><!-- .chat-panel -->

            </div><!-- .messages-layout -->

        </div><!-- .content -->
    </div><!-- .main -->
</div><!-- .layout -->

<!-- ================================================================ -->
<!-- JAVASCRIPT — Message Send (Frontend Demo)                        -->
<!-- ================================================================ -->
<script>
    const area = document.getElementById('messagesArea');
    area.scrollTop = area.scrollHeight;

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        if (!text) return;

        // Frontend demo — append bubble immediately
        appendMessage(text);
        input.value = '';
    }

    function appendMessage(text) {
        const now = new Date();
        const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

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
</script>
</body>
</html>