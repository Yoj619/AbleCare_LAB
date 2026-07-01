<?php
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'healthcare_provider') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
$db = get_db();

// Load current provider data from DB
$_stmt = $db->prepare(
    'SELECT u.first_name, u.last_name, u.email, u.phone_number, u.profile_photo_path,
            hp.specialization, hp.license_number, c.name AS clinic_name
     FROM users u
     LEFT JOIN healthcare_providers hp ON hp.user_id = u.id
     LEFT JOIN clinics c ON c.id = hp.clinic_id
     WHERE u.id = ? LIMIT 1'
);
$_stmt->bind_param('i', $_SESSION['user_id']);
$_stmt->execute();
$_row = $_stmt->get_result()->fetch_assoc();
$_stmt->close();

$_fn = $_row['first_name'] ?? '';
$_ln = $_row['last_name']  ?? '';
$initials_prov = strtoupper(substr($_fn, 0, 1) . substr($_ln, 0, 1)) ?: 'P';

$provider = [
    'name'           => trim($_fn . ' ' . $_ln) ?: ($_SESSION['full_name'] ?? 'Provider'),
    'first_name'     => $_fn,
    'last_name'      => $_ln,
    'role'           => 'Healthcare Provider',
    'hospital'       => $_row['clinic_name'] ?? '',
    'avatar'         => !empty($_row['profile_photo_path']) ? '/AbleCare/' . $_row['profile_photo_path'] : ($_SESSION['avatar'] ?? ''),
    'specialty'      => $_row['specialization'] ?? '',
    'license_number' => $_row['license_number'] ?? '',
    'contact_number' => $_row['phone_number'] ?? '',
    'email'          => $_row['email'] ?? ($_SESSION['email'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AbleCare — Account Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/provider_settings.css">
</head>
<body>
<div class="layout">

    <!-- ================================================================ -->
    <!-- SIDEBAR (with logo only, no green background)                    -->
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
            <a href="message.php" class="nav-item">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg></span>
                Messages
            </a>
            <a href="provider_settings.php" class="nav-item active">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg></span>
                Account Settings
            </a>
        </nav>

        <div class="sidebar-logout">
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout of AbleCare?')">
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
                <?php if (!empty($provider['avatar'])): ?>
                <img src="<?= htmlspecialchars($provider['avatar']) ?>" alt="Avatar" class="avatar" />
                <?php else: ?>
                <div class="avatar" style="display:flex;align-items:center;justify-content:center;background:#d4efed;color:#3aafa9;font-weight:700;font-size:16px;"><?= htmlspecialchars($initials_prov) ?></div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Content -->
        <div class="content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <svg viewBox="0 0 24 24" fill="#26a69a"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg>
                    Account Settings
                </div>
                <div class="breadcrumb">
                    <a href="dashboard_provider.php">Dashboard</a>
                    <span>›</span>
                    Account Settings
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- PROFESSIONAL PROFILE CARD                                     -->
            <!-- ============================================================ -->
            <div class="settings-card">


                <div class="card-header">
                    <div class="card-icon teal">
                        <svg viewBox="0 0 24 24"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg>
                    </div>
                    <div class="card-header-text">
                        <div class="card-title">Professional Profile</div>
                    </div>
                </div>

                <div id="profileSuccess" class="alert alert-success" style="display:none;">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <span id="profileSuccessMsg">Profile updated successfully.</span>
                </div>
                <div id="profileErrAlert" class="alert alert-error" style="display:none;"></div>

                <form id="profileForm" enctype="multipart/form-data">

                    <!-- Profile photo row -->
                    <div style="display:flex;align-items:center;gap:18px;margin-bottom:20px;">
                        <div id="provAvatarWrap" style="width:72px;height:72px;border-radius:50%;overflow:hidden;background:#d4efed;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid #3aafa9;">
                            <?php if (!empty($provider['avatar'])): ?>
                                <img src="<?= htmlspecialchars($provider['avatar']) ?>" alt="Photo" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <span style="font-size:24px;font-weight:700;color:#3aafa9;"><?= htmlspecialchars($initials_prov) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label" style="display:block;margin-bottom:6px;">Profile Photo</label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="font-size:13px;" onchange="previewProvPhoto(this)">
                            <div style="font-size:12px;color:#999;margin-top:4px;">JPG, PNG or WebP · Max 2 MB</div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                class="form-input"
                                value="<?= htmlspecialchars($provider['name']) ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="specialty">Specialty/Designation <span class="required">*</span></label>
                            <input
                                type="text"
                                id="specialty"
                                name="specialization"
                                class="form-input"
                                value="<?= htmlspecialchars($provider['specialty']) ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="license_number">License Number <span class="required">*</span></label>
                            <input
                                type="text"
                                id="license_number"
                                name="license_number"
                                class="form-input"
                                value="<?= htmlspecialchars($provider['license_number']) ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact_number">Contact Number <span class="required">*</span></label>
                            <input
                                type="text"
                                id="contact_number"
                                name="contact_number"
                                class="form-input"
                                value="<?= htmlspecialchars($provider['contact_number']) ?>"
                                required
                            />
                        </div>
                        <div class="form-group full">
                            <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                value="<?= htmlspecialchars($provider['email']) ?>"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-save" onclick="submitProvProfile()">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                            Save Changes
                        </button>
                        <button type="button" class="btn-cancel" onclick="resetProvProfileForm()">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            Cancel
                        </button>
                    </div>

                </form>
            </div>

            <!-- ============================================================ -->
            <!-- SECURITY SETTINGS CARD                                        -->
            <!-- ============================================================ -->
            <div class="settings-card">

                <div id="pwSuccess" class="alert alert-success" style="display:none;">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Password updated successfully.
                </div>
                <div id="pwErrAlert" class="alert alert-error" style="display:none;"></div>

                <div class="card-header">
                    <div class="card-icon blue">
                        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    </div>
                    <div class="card-header-text">
                        <div class="card-title">Security Settings</div>
                        <div class="card-subtitle">Update your password to keep your account secure</div>
                    </div>
                </div>

                <form id="pwForm">
                    <input type="hidden" name="action" value="update_password" />

                    <div class="pw-fields">
                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password <span class="required">*</span></label>
                            <div class="password-wrap">
                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-input"
                                    placeholder="Enter current password"
                                    required
                                />
                                <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)" aria-label="Show/hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="new_password">New Password <span class="required">*</span></label>
                            <div class="password-wrap">
                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="form-input"
                                    placeholder="Enter new password"
                                    required
                                />
                                <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)" aria-label="Show/hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm New Password <span class="required">*</span></label>
                            <div class="password-wrap">
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-input"
                                    placeholder="Confirm new password"
                                    required
                                />
                                <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)" aria-label="Show/hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-save" onclick="submitProvPassword()">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                            Update Password
                        </button>
                        <button type="button" class="btn-cancel" onclick="resetProvPasswordForm()">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            Cancel
                        </button>
                    </div>

                </form>
            </div>

        </div><!-- .content -->
    </div><!-- .main -->
</div><!-- .layout -->

<!-- ================================================================ -->
<!-- JAVASCRIPT                                                       -->
<!-- ================================================================ -->
<script>
    const PROV_API = '/AbleCare/backend/api/users/update-profile.php';

    function previewProvPhoto(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('provAvatarWrap').innerHTML =
                `<img src="${e.target.result}" alt="Photo" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }

    async function submitProvProfile() {
        if (!confirm('Are you sure you want to save these changes?')) return;
        const succ = document.getElementById('profileSuccess');
        const err  = document.getElementById('profileErrAlert');
        succ.style.display = 'none';
        err.style.display  = 'none';

        const fd = new FormData(document.getElementById('profileForm'));
        fd.append('action', 'update_profile');

        const parts = (fd.get('full_name') || '').trim().split(/\s+/);
        fd.append('first_name', parts[0] || '');
        fd.append('last_name',  parts.slice(1).join(' ') || parts[0] || '');

        const res  = await fetch(PROV_API, { method: 'POST', body: fd });
        const json = await res.json();

        if (json.error) {
            err.textContent   = json.error;
            err.style.display = 'flex';
            return;
        }

        document.getElementById('profileSuccessMsg').textContent = json.data.message;
        succ.style.display = 'flex';
        if (json.data.avatar) {
            document.getElementById('provAvatarWrap').innerHTML =
                `<img src="${json.data.avatar}" alt="Photo" style="width:100%;height:100%;object-fit:cover;">`;
        }
        setTimeout(() => { succ.style.display = 'none'; }, 4000);
    }

    function resetProvProfileForm() {
        document.getElementById('full_name').value      = '<?= addslashes($provider['name']) ?>';
        document.getElementById('specialty').value      = '<?= addslashes($provider['specialty']) ?>';
        document.getElementById('license_number').value = '<?= addslashes($provider['license_number']) ?>';
        document.getElementById('contact_number').value = '<?= addslashes($provider['contact_number']) ?>';
        document.getElementById('email').value          = '<?= addslashes($provider['email']) ?>';
        document.getElementById('profileErrAlert').style.display  = 'none';
        document.getElementById('profileSuccess').style.display   = 'none';
    }

    function togglePw(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.style.color = isPassword ? '#26a69a' : '#9ca3af';
    }

    async function submitProvPassword() {
        if (!confirm('Are you sure you want to save these changes?')) return;
        const succ = document.getElementById('pwSuccess');
        const err  = document.getElementById('pwErrAlert');
        succ.style.display = 'none';
        err.style.display  = 'none';

        const fd = new FormData(document.getElementById('pwForm'));
        fd.append('action', 'update_password');

        const res  = await fetch(PROV_API, { method: 'POST', body: fd });
        const json = await res.json();

        if (json.error) {
            err.textContent   = json.error;
            err.style.display = 'flex';
            return;
        }

        succ.style.display = 'flex';
        document.getElementById('pwForm').reset();
        setTimeout(() => { succ.style.display = 'none'; }, 4000);
    }

    function resetProvPasswordForm() {
        document.getElementById('pwForm').reset();
        document.getElementById('pwErrAlert').style.display = 'none';
        document.getElementById('pwSuccess').style.display  = 'none';
    }
</script>
</body>
</html>