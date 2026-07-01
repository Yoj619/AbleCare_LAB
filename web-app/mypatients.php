<?php
/**
 * AbleCare - Healthcare Provider Portal
 * My Patients Page
 */

$provider = [
    'name'     => $_SESSION['full_name'] ?? 'Healthcare Provider',
    'role'     => $_SESSION['role'] ?? 'Healthcare Provider',
    'hospital' => $_SESSION['hospital'] ?? '',
    'avatar'   => $_SESSION['avatar'] ?? '',
];

// TODO: replace with DB query (SELECT * FROM patients WHERE provider_id = ?) when table exists
$patients = [];

$totalPatients = count($patients);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Patients — AbleCare Provider Portal</title>
    <link rel="stylesheet" href="css/mypatients.css">
</head>
<body>
<div class="layout view-grid" id="layoutRoot">

    <!-- SIDEBAR -->
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
            <a href="mypatients.php" class="nav-item active">
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
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg></span>
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

    <!-- MAIN -->
    <div class="main">
        <header class="topbar">
            <div>
                <div class="topbar-title">Healthcare Provider Portal</div>
                <div class="topbar-sub"><?= htmlspecialchars($provider['hospital']) ?></div>
            </div>
            <div class="topbar-right">
                <div class="topbar-bell" title="Notifications">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="color:#9ca3af"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                </div>
                <div class="topbar-user">
                    <div class="topbar-user-info">
                        <div class="topbar-user-name"><?= htmlspecialchars($provider['name']) ?></div>
                        <div class="topbar-user-role"><?= htmlspecialchars($provider['role']) ?></div>
                    </div>
                    <img src="<?= $provider['avatar'] ?>" alt="Avatar" class="avatar" />
                </div>
            </div>
        </header>

        <div class="content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title-row">
                    <svg class="page-title-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <span class="page-title">My Patients</span>
                    <span class="badge-total"><?= $totalPatients ?> Total</span>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" id="btnGrid" title="Grid View" onclick="setView('grid')">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h7v7H3zm0 11h7v7H3zm11-11h7v7h-7zm0 11h7v7h-7z"/></svg>
                    </button>
                    <button class="view-btn" id="btnList" title="List View" onclick="setView('list')">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
                    </button>
                </div>
            </div>

            <div class="breadcrumb">
                <a href="dashboard_provider.php">Dashboard</a> &rsaquo; <span>My Patients</span>
            </div>

            <!-- Filter Bar -->
            <div class="filter-card">
                <div class="filter-group">
                    <label class="filter-label">Search Patients</label>
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                        <input type="text" class="search-input" id="searchInput" placeholder="Search by name or condition..." oninput="filterPatients()" />
                    </div>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter" onchange="filterPatients()">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Disability Type</label>
                    <select class="filter-select" id="disabilityFilter" onchange="filterPatients()">
                        <option value="">All Disability Types</option>
                        <option value="Physical">Physical</option>
                        <option value="Respiratory">Respiratory</option>
                        <option value="Cardiac">Cardiac</option>
                    </select>
                </div>
            </div>

            <!-- GRID VIEW -->
            <div class="grid-view">
                <div class="cards-grid" id="cardsGrid">
                    <?php if (empty($patients)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        <p>No patients assigned yet.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($patients as $p): ?>
                    <div class="patient-card"
                         data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
                         data-condition="<?= strtolower(htmlspecialchars($p['condition'])) ?>"
                         data-status="<?= htmlspecialchars($p['status']) ?>"
                         data-disability="<?= htmlspecialchars($p['disability_type']) ?>">
                        <div class="card-status-badge">
                            <span class="status-pill status-<?= $p['status'] ?>"><?= $p['status'] ?></span>
                        </div>
                        <img src="<?= $p['avatar'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-avatar" />
                        <div class="card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="card-age"><?= $p['age'] ?> years old</div>
                        <div class="card-detail">
                            <span class="card-detail-label">Condition:</span>
                            <span class="card-detail-value"><?= htmlspecialchars($p['condition']) ?></span>
                        </div>
                        <div class="card-detail">
                            <span class="card-detail-label">Caregiver:</span>
                            <span class="card-detail-value"><?= htmlspecialchars($p['caregiver']) ?></span>
                        </div>
                        <div class="card-detail">
                            <span class="card-detail-label">Last Visit:</span>
                            <span class="card-detail-value"><?= htmlspecialchars($p['last_visit']) ?></span>
                        </div>
                        <button class="btn-view-profile"
                            onclick="openProfileModal('<?= htmlspecialchars(addslashes($p['name'])) ?>',<?= $p['age'] ?>,'<?= htmlspecialchars(addslashes($p['condition'])) ?>','<?= htmlspecialchars(addslashes($p['caregiver'])) ?>','<?= htmlspecialchars(addslashes($p['last_visit'])) ?>','<?= $p['status'] ?>')">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                            View Profile
                        </button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LIST VIEW -->
            <div class="list-view">
                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Age</th>
                                <th>Condition</th>
                                <th>Caregiver</th>
                                <th>Last Visit</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="patientsTableBody">
                        <?php if (empty($patients)): ?>
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                    <p>No patients assigned yet.</p>
                                </div>
                            </td></tr>
                        <?php else: ?>
                        <?php foreach ($patients as $p): ?>
                            <tr data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
                                data-condition="<?= strtolower(htmlspecialchars($p['condition'])) ?>"
                                data-status="<?= htmlspecialchars($p['status']) ?>"
                                data-disability="<?= htmlspecialchars($p['disability_type']) ?>">
                                <td><span class="patient-name"><?= htmlspecialchars($p['name']) ?></span></td>
                                <td><span class="patient-age"><?= $p['age'] ?></span></td>
                                <td><?= htmlspecialchars($p['condition']) ?></td>
                                <td><?= htmlspecialchars($p['caregiver']) ?></td>
                                <td><?= htmlspecialchars($p['last_visit']) ?></td>
                                <td><span class="status-pill status-<?= $p['status'] ?>"><?= $p['status'] ?></span></td>
                                <td>
                                    <button class="btn-view-eye" title="View Profile"
                                        onclick="openProfileModal('<?= htmlspecialchars(addslashes($p['name'])) ?>',<?= $p['age'] ?>,'<?= htmlspecialchars(addslashes($p['condition'])) ?>','<?= htmlspecialchars(addslashes($p['caregiver'])) ?>','<?= htmlspecialchars(addslashes($p['last_visit'])) ?>','<?= $p['status'] ?>')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
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

<!-- Patient Profile Modal -->
<div class="modal-overlay" id="modalProfile">
    <div class="modal">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-title">Patient Profile</div>
        <div class="detail-field">
            <div class="detail-label">Patient Name</div>
            <div class="detail-value big" id="modalName"></div>
        </div>
        <div class="detail-row">
            <div class="detail-field">
                <div class="detail-label">Age</div>
                <div class="detail-value" id="modalAge"></div>
            </div>
            <div class="detail-field">
                <div class="detail-label">Status</div>
                <div class="detail-value" id="modalStatus"></div>
            </div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Medical Condition</div>
            <div class="detail-value" id="modalCondition"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Caregiver</div>
            <div class="detail-value" id="modalCaregiver"></div>
        </div>
        <div class="detail-field">
            <div class="detail-label">Last Visit</div>
            <div class="detail-value" id="modalLastVisit"></div>
        </div>
        <div class="modal-actions">
            <button class="btn-close-modal" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
    const root    = document.getElementById('layoutRoot');
    const btnGrid = document.getElementById('btnGrid');
    const btnList = document.getElementById('btnList');

    function setView(mode) {
        if (mode === 'grid') {
            root.classList.remove('view-list');
            root.classList.add('view-grid');
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        } else {
            root.classList.remove('view-grid');
            root.classList.add('view-list');
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
        }
    }

    function filterPatients() {
        const search = document.getElementById('searchInput').value.toLowerCase().trim();
        const status = document.getElementById('statusFilter').value;
        const disab  = document.getElementById('disabilityFilter').value;

        document.querySelectorAll('#cardsGrid .patient-card').forEach(card => {
            const ok = (!search || card.dataset.name.includes(search) || card.dataset.condition.includes(search))
                    && (!status || card.dataset.status === status)
                    && (!disab  || card.dataset.disability === disab);
            card.style.display = ok ? '' : 'none';
        });

        document.querySelectorAll('#patientsTableBody tr').forEach(row => {
            const ok = (!search || row.dataset.name.includes(search) || row.dataset.condition.includes(search))
                    && (!status || row.dataset.status === status)
                    && (!disab  || row.dataset.disability === disab);
            row.style.display = ok ? '' : 'none';
        });
    }

    function openProfileModal(name, age, condition, caregiver, lastVisit, status) {
        document.getElementById('modalName').textContent      = name;
        document.getElementById('modalAge').textContent       = age + ' years old';
        document.getElementById('modalCondition').textContent = condition;
        document.getElementById('modalCaregiver').textContent = caregiver;
        document.getElementById('modalLastVisit').textContent = lastVisit;
        document.getElementById('modalStatus').innerHTML      = '<span class="status-pill status-' + status + '">' + status + '</span>';
        document.getElementById('modalProfile').classList.add('open');
    }

    function closeModal() {
        document.getElementById('modalProfile').classList.remove('open');
    }

    document.getElementById('modalProfile').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
</body>
</html>