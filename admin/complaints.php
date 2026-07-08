<?php
/**
 * admin/complaints.php — Admin Complaints Manager
 * View, filter, and process all customer complaints.
 * PROTECTED: Admin role required.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin(); // 🛡️ Admin only

$pdo = get_db_connection();

// ── Query all complaints ─────────────────────────────────────────────────────
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               u.name AS customer_name, u.email AS customer_email,
               p.name AS product_name
        FROM complaints c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN products p ON c.product_id = p.id
        ORDER BY c.created_at DESC
    ");
    $complaints = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error loading complaints: " . $e->getMessage());
}

$page_title = 'Complaints Manager';
$page_class = 'admin-layout';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* Hide standard elements */
body.admin-layout .navbar { display: none !important; }
body.admin-layout .site-footer { display: none !important; }

/* Grid container layout */
.db-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: #F7F7F8;
    color: #4B5563;
    font-family: 'Inter', sans-serif;
}

/* Sidebar styling */
.db-sidebar {
    background: #FFFFFF;
    border-right: 1px solid #E6E6E6;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100vh;
    position: sticky;
    top: 0;
    padding: 1.5rem 0;
}

.db-sidebar__top {
    padding: 0 1.5rem;
}

.db-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: #FF7A1A;
    text-decoration: none;
    margin-bottom: 2rem;
}
.db-logo span {
    color: #0B0B0B;
}

.db-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.db-menu__item a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    color: #4B5563;
    font-size: 0.9375rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s ease;
}

.db-menu__item.active a {
    background: rgba(255, 122, 26, 0.08);
    color: #FF7A1A;
    font-weight: 600;
}

.db-menu__item a:hover:not(.active) {
    background: #F3F4F6;
    color: #0B0B0B;
}

.db-sidebar__bottom {
    padding: 1rem 1.5rem;
    border-top: 1px solid #E6E6E6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.db-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.db-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #FF7A1A;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.db-user-text {
    display: flex;
    flex-direction: column;
}

.db-user-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #0B0B0B;
}

.db-user-role {
    font-size: 0.75rem;
    color: #9CA3AF;
}

/* Main Content Area */
.db-main {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow-y: auto;
}

/* Topbar */
.db-topbar {
    background: #FFFFFF;
    border-bottom: 1px solid #E6E6E6;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 10;
}

.db-topbar__title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #0B0B0B;
}

.db-topbar__actions {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.db-search-wrap {
    position: relative;
    width: 320px;
}

.db-search-icon {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9CA3AF;
    pointer-events: none;
    display: flex;
}

.db-search-input {
    width: 100%;
    padding: 0.5rem 1rem 0.5rem 2.25rem;
    border: 1px solid #E6E6E6;
    border-radius: 8px;
    font-size: 0.875rem;
    outline: none;
    background: #F9FAFB;
    transition: all 0.15s ease;
}

.db-search-input:focus {
    background: #FFFFFF;
    border-color: #FF7A1A;
    box-shadow: 0 0 0 3px rgba(255, 122, 26, 0.1);
}

/* Dashboard Body Content */
.db-body {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    flex: 1;
}

/* Panel card */
.db-panel-card {
    background: #FFFFFF;
    border: 1px solid #E6E6E6;
    border-radius: 12px;
    padding: 1.75rem;
}

.db-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.db-panel-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0B0B0B;
}

.db-panel-subtitle {
    font-size: 0.8125rem;
    color: #9CA3AF;
    margin-top: 0.25rem;
}

/* Filtering Tabs */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 1px solid #E6E6E6;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    flex-wrap: wrap;
}

.filter-tab {
    background: none;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6B7280;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.15s ease;
}

.filter-tab.active {
    background: rgba(255, 122, 26, 0.08);
    color: #FF7A1A;
}

.filter-tab:hover:not(.active) {
    background: #F3F4F6;
    color: #0B0B0B;
}

/* Custom Styled Table */
.db-table-wrap {
    overflow-x: auto;
}

.db-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.db-table th {
    font-size: 0.75rem;
    font-weight: 600;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #E6E6E6;
}

.db-table td {
    padding: 1rem;
    border-bottom: 1px solid #E6E6E6;
    font-size: 0.875rem;
    vertical-align: middle;
}

.db-table tr:hover td {
    background: #F9FAFB;
}

.db-table tr:last-child td {
    border-bottom: none;
}

/* Custom Customer Avatar + Info layout */
.db-customer-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.db-customer-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #F3F4F6;
    color: #4B5563;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8125rem;
}

.db-customer-name {
    font-weight: 600;
    color: #0B0B0B;
}

.db-customer-code {
    font-size: 0.75rem;
    color: #9CA3AF;
}

/* Badges */
.badge-status {
    display: inline-flex;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-status.new { background: #FFF7ED; color: #EA580C; }
.badge-status.in_progress { background: #FEF3C7; color: #D97706; }
.badge-status.resolved { background: #ECFDF5; color: #059669; }
.badge-status.closed { background: #F3F4F6; color: #4B5563; }

.priority-dot {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 500;
}

.priority-dot::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.priority-dot.high::before { background: #EF4444; }
.priority-dot.medium::before { background: #F59E0B; }
.priority-dot.low::before { background: #10B981; }

/* Pagination Area */
.db-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0 0;
    margin-top: 1rem;
    font-size: 0.8125rem;
    color: #9CA3AF;
}

.db-page-btn-group {
    display: flex;
    gap: 0.25rem;
}

.db-page-btn {
    border: 1px solid #E6E6E6;
    background: #FFFFFF;
    color: #4B5563;
    padding: 0.25rem 0.5rem;
    min-width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.15s ease;
}

.db-page-btn.active {
    background: #FF7A1A;
    color: #FFFFFF;
    border-color: #FF7A1A;
    font-weight: 600;
}

.db-page-btn:hover:not(.active) {
    background: #F3F4F6;
    color: #0B0B0B;
}

.db-btn-secondary-sm {
    background: #FFFFFF;
    border: 1px solid #E6E6E6;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #4B5563;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.15s ease;
}

.db-btn-secondary-sm:hover {
    background: #F9FAFB;
    border-color: #D1D5DB;
    color: #0B0B0B;
}

/* Custom Footer bar */
.db-footer-bar {
    border-top: 1px solid #E6E6E6;
    padding: 1.5rem 2rem;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8125rem;
    color: #9CA3AF;
    margin-top: auto;
}

.db-footer-links {
    display: flex;
    gap: 1.5rem;
}

.db-footer-links a {
    color: #9CA3AF;
    text-decoration: none;
    transition: color 0.15s;
}

.db-footer-links a:hover {
    color: #4B5563;
}
</style>

<div class="db-grid">

    <!-- ─── SIDEBAR (LEFT) ─── -->
    <aside class="db-sidebar">
        <div class="db-sidebar__top">
            <!-- Brand Logo -->
            <a href="<?= BASE_PATH ?>/index.php" class="db-logo">
                🍔 <span>FoodCare</span>
            </a>

            <!-- Navigation Links -->
            <ul class="db-menu">
                <li class="db-menu__item">
                    <a href="<?= BASE_PATH ?>/admin/dashboard.php">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                        Dashboard
                    </a>
                </li>
                <li class="db-menu__item active">
                    <a href="<?= BASE_PATH ?>/admin/complaints.php">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        Complaints
                    </a>
                </li>
                <li class="db-menu__item">
                    <a href="<?= BASE_PATH ?>/admin/products.php">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Products
                    </a>
                </li>
                <li class="db-menu__item">
                    <a href="<?= BASE_PATH ?>/admin/users.php">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Users
                    </a>
                </li>
                <li class="db-menu__item">
                    <a href="#" onclick="alert('Reports generator is currently processing compiled database logs. Try again later.'); return false;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        Reports
                    </a>
                </li>
                <li class="db-menu__item">
                    <a href="#" onclick="alert('Access restricted to System Root Administrators.'); return false;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Settings
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sidebar User Footer -->
        <div class="db-sidebar__bottom">
            <div class="db-user-info">
                <div class="db-user-avatar">
                    <?= strtoupper(substr(get_current_user_name(), 0, 1)) ?>
                </div>
                <div class="db-user-text">
                    <span class="db-user-name"><?= htmlspecialchars(get_current_user_name()) ?></span>
                    <span class="db-user-role">Super Admin</span>
                </div>
            </div>
            <a href="<?= BASE_PATH ?>/logout.php" title="Logout" style="color:#9CA3AF; display:flex; transition:color 0.15s;" onmouseover="this.style.color='#EF4444'" onmouseout="this.style.color='#9CA3AF'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </aside>

    <!-- ─── MAIN CONTENT ─── -->
    <main class="db-main">

        <!-- Top Header Bar -->
        <header class="db-topbar">
            <h1 class="db-topbar__title">Complaints Manager</h1>

            <div class="db-topbar__actions">
                <!-- Search bar -->
                <div class="db-search-wrap">
                    <span class="db-search-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" id="db-search" class="db-search-input" placeholder="Search customer, code, product..." onkeyup="filterComplaintsTable()">
                </div>
            </div>
        </header>

        <!-- Body Scroll Area -->
        <div class="db-body">

            <!-- Complaints Manager Panel -->
            <div class="db-panel-card">
                <div class="db-panel-header">
                    <div>
                        <h2 class="db-panel-title">All Registered Complaints</h2>
                        <p class="db-panel-subtitle">Review status, view attachments, update tracking details, and issue resolutions.</p>
                    </div>
                    <div>
                        <button class="db-btn-secondary-sm" onclick="exportComplaintsCSV()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Export CSV
                        </button>
                    </div>
                </div>

                <!-- Status Tabs -->
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterByStatus('all', this)">All</button>
                    <button class="filter-tab" onclick="filterByStatus('new', this)">New</button>
                    <button class="filter-tab" onclick="filterByStatus('in_progress', this)">In Progress</button>
                    <button class="filter-tab" onclick="filterByStatus('resolved', this)">Resolved</button>
                    <button class="filter-tab" onclick="filterByStatus('closed', this)">Closed</button>
                </div>

                <!-- Advanced Filter Controls -->
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; padding:1rem 1.5rem; background:#F9FAFB; border-bottom:1px solid #E6E6E6; align-items:center;">
                    <span style="font-size:0.75rem; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.05em;">Filters:</span>
                    
                    <select id="filter-category" onchange="filterComplaintsTable()" style="padding:0.35rem 0.75rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.8rem; outline:none; background:#FFF; cursor:pointer;">
                        <option value="all">All Categories</option>
                        <option value="food quality">Food Quality</option>
                        <option value="packaging">Packaging</option>
                        <option value="delivery">Delivery</option>
                        <option value="hygiene">Hygiene</option>
                        <option value="other">Other</option>
                    </select>

                    <select id="filter-priority" onchange="filterComplaintsTable()" style="padding:0.35rem 0.75rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.8rem; outline:none; background:#FFF; cursor:pointer;">
                        <option value="all">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>

                    <input type="date" id="filter-date" onchange="filterComplaintsTable()" style="padding:0.3rem 0.5rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.8rem; outline:none; background:#FFF; cursor:pointer;">
                    
                    <button onclick="resetAdminFilters()" style="padding:0.35rem 0.75rem; border:1px solid #D1D5DB; background:#FFF; border-radius:6px; font-size:0.8rem; cursor:pointer; color:#4B5563; font-weight:500; transition:all 0.15s; margin-left:auto;">
                        Clear Filters
                    </button>
                </div>

                <!-- Table -->
                <div class="db-table-wrap">
                    <table class="db-table" id="db-complaints-table">
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" onclick="toggleSelectAll(this)"></th>
                                <th>Complaint Code</th>
                                <th>Customer Name</th>
                                <th>Category</th>
                                <th>Product Item</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Submitted Date</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($complaints)): ?>
                                <tr>
                                    <td colspan="9" style="text-align:center; padding:3rem; color:#9CA3AF;">
                                        No complaints registered in the system yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $c): ?>
                                    <tr class="complaint-row" 
                                        data-customer="<?= htmlspecialchars(strtolower($c['customer_name'])) ?>"
                                        data-product="<?= htmlspecialchars(strtolower($c['product_name'] ?? 'general')) ?>"
                                        data-category="<?= htmlspecialchars(strtolower($c['category'])) ?>"
                                        data-status="<?= htmlspecialchars($c['status']) ?>"
                                        data-priority="<?= htmlspecialchars(strtolower($c['priority'])) ?>"
                                        data-subdate="<?= date('Y-m-d', strtotime($c['created_at'])) ?>"
                                        data-upddate="<?= date('Y-m-d', strtotime($c['updated_at'])) ?>">
                                        
                                        <td><input type="checkbox" class="row-checkbox"></td>
                                        <td><strong>CMP-<?= str_pad($c['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td>
                                            <div class="db-customer-row">
                                                <div class="db-customer-avatar" style="background:#FF7A1A; color:#fff;">
                                                    <?= strtoupper(substr($c['customer_name'], 0, 1)) ?>
                                                </div>
                                                <div class="db-customer-text">
                                                    <span class="db-customer-name"><?= htmlspecialchars($c['customer_name']) ?></span>
                                                    <span class="db-customer-code"><?= htmlspecialchars($c['customer_email']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span style="font-weight:500;"><?= htmlspecialchars($c['category']) ?></span></td>
                                        <td><span style="color:#0B0B0B; font-weight:500;"><?= htmlspecialchars($c['product_name'] ?? 'General / Other') ?></span></td>
                                        <td>
                                            <span class="badge-status <?= $c['status'] ?>">
                                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $c['status']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="priority-dot <?= strtolower($c['priority']) ?>">
                                                <?= htmlspecialchars($c['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size:0.875rem; color:#4B5563;"><?= date('Y-m-d', strtotime($c['created_at'])) ?></span>
                                        </td>
                                        <td>
                                            <span style="font-size:0.875rem; color:#4B5563; font-weight:500;"><?= date('Y-m-d', strtotime($c['updated_at'])) ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_PATH ?>/admin/complaint_details.php?id=<?= $c['id'] ?>" class="db-btn-secondary-sm" style="padding: 0.25rem 0.5rem;">
                                                Process
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="db-pagination">
                    <span id="pagination-text">Showing 1-<?= min(10, count($complaints)) ?> of <?= count($complaints) ?> complaints</span>
                    <div class="db-page-btn-group">
                        <button class="db-page-btn" onclick="prevPage()">Prev</button>
                        <button class="db-page-btn active" onclick="setPage(1)">1</button>
                        <?php if (count($complaints) > 10): ?>
                            <button class="db-page-btn" onclick="setPage(2)">2</button>
                        <?php endif; ?>
                        <button class="db-page-btn" onclick="nextPage()">Next</button>
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer -->
        <footer class="db-footer-bar">
            <span>© <?= date('Y') ?> FoodCare Admin Portal. All rights reserved.</span>
            <div class="db-footer-links">
                <a href="#">Support</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </footer>

    </main>

</div>

<script>
let currentStatusFilter = 'all';

// Scroll helper
function scrollToElement(id) {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}

// Live table search & advanced filtering
function filterComplaintsTable() {
    const searchVal = document.getElementById('db-search') ? document.getElementById('db-search').value.toLowerCase() : '';
    const selCat = document.getElementById('filter-category') ? document.getElementById('filter-category').value.toLowerCase() : 'all';
    const selPriority = document.getElementById('filter-priority') ? document.getElementById('filter-priority').value.toLowerCase() : 'all';
    const selDate = document.getElementById('filter-date') ? document.getElementById('filter-date').value : '';
    
    const rows = document.querySelectorAll('.complaint-row');
    
    rows.forEach(row => {
        const customer = row.getAttribute('data-customer');
        const product = row.getAttribute('data-product');
        const category = row.getAttribute('data-category');
        const priority = row.getAttribute('data-priority');
        const status = row.getAttribute('data-status');
        const subDate = row.getAttribute('data-subdate');
        const updDate = row.getAttribute('data-upddate');
        
        const matchesSearch = customer.includes(searchVal) || product.includes(searchVal) || category.includes(searchVal) || priority.includes(searchVal);
        const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;
        
        const matchesCat = selCat === 'all' || category === selCat;
        const matchesPriority = selPriority === 'all' || priority === selPriority;
        
        let matchesDate = true;
        if (selDate) {
            matchesDate = (subDate === selDate || updDate === selDate);
        }
        
        if (matchesSearch && matchesStatus && matchesCat && matchesPriority && matchesDate) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function resetAdminFilters() {
    if (document.getElementById('filter-category')) document.getElementById('filter-category').value = 'all';
    if (document.getElementById('filter-priority')) document.getElementById('filter-priority').value = 'all';
    if (document.getElementById('filter-date')) document.getElementById('filter-date').value = '';
    filterComplaintsTable();
}

// Status tab filtering
function filterByStatus(status, tabElement) {
    // Update active tab styling
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    tabElement.classList.add('active');
    
    currentStatusFilter = status;
    filterComplaintsTable();
}

// Multi-select actions
function toggleSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
}

// Simple export CSV simulation
function exportComplaintsCSV() {
    let csv = 'Complaint ID,Customer,Email,Category,Product,Status,Priority,Submitted Date,Last Updated\n';
    const rows = document.querySelectorAll('.complaint-row');
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const code = row.querySelector('td:nth-child(2)').innerText.trim();
            const customer = row.querySelector('.db-customer-name').innerText.trim();
            const email = row.querySelector('.db-customer-code').innerText.trim();
            const category = row.querySelector('td:nth-child(4)').innerText.trim();
            const product = row.querySelector('td:nth-child(5)').innerText.trim();
            const status = row.querySelector('.badge-status').innerText.trim();
            const priority = row.querySelector('.priority-dot').innerText.trim();
            const subdate = row.querySelector('td:nth-child(8)').innerText.trim();
            const upddate = row.querySelector('td:nth-child(9)').innerText.trim();
            csv += `"${code}","${customer}","${email}","${category}","${product}","${status}","${priority}","${subdate}","${upddate}"\n`;
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "foodcare_complaints_manager_export.csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
