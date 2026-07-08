<?php
/**
 * admin/users.php — Admin Users Management
 * View and search all registered user accounts.
 * PROTECTED: Admin role required.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin(); // 🛡️ Admin only

$pdo = get_db_connection();

// ── Fetch all registered users ───────────────────────────────────────────────
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error loading users: " . $e->getMessage());
}

$page_title = 'User Accounts';
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
    font-size: 1.25rem;
    font-weight: 700;
    color: #FF7A1A;
    text-decoration: none;
    margin-bottom: 2rem;
}

.db-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.db-menu__item {
    margin-bottom: 0.25rem;
}

.db-menu__item a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #4B5563;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s ease;
}

.db-menu__item a:hover {
    background: #F3F4F6;
    color: #111827;
}

.db-menu__item.active a {
    background: #FFF0E6;
    color: #FF7A1A;
    font-weight: 600;
}

.db-sidebar__bottom {
    border-top: 1px solid #E6E6E6;
    padding: 1.5rem;
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
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #FF7A1A;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.db-user-text {
    display: flex;
    flex-direction: column;
}

.db-user-name {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #1F2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 110px;
}

.db-user-role {
    font-size: 0.6875rem;
    color: #6B7280;
}

/* Main content workspace */
.db-main {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

.db-topbar {
    background: #FFFFFF;
    border-bottom: 1px solid #E6E6E6;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 64px;
    flex-shrink: 0;
}

.db-topbar__title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.db-btn-secondary-sm {
    background: #FFFFFF;
    border: 1px solid #D1D5DB;
    color: #374151;
    font-size: 0.8125rem;
    font-weight: 500;
    border-radius: 6px;
    padding: 0.375rem 0.75rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.db-btn-secondary-sm:hover {
    background: #F9FAFB;
    border-color: #C1C5CB;
}

.db-body {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
}

/* UI Panel Cards */
.db-panel-card {
    background: #FFFFFF;
    border: 1px solid #E6E6E6;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    margin-bottom: 1.5rem;
}

.db-panel-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
    margin-top: 0;
    margin-bottom: 1rem;
}

/* Table styling */
.db-table-wrap {
    overflow-x: auto;
}

.db-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 0.8125rem;
}

.db-table th {
    background: #F9FAFB;
    border-bottom: 1px solid #E6E6E6;
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: #4B5563;
    text-transform: uppercase;
    font-size: 0.6875rem;
    letter-spacing: 0.05em;
}

.db-table td {
    padding: 1rem;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: middle;
    color: #4B5563;
}

.db-table tr:hover td {
    background: #FAFAFB;
}

/* Badge status indicators */
.badge-role {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-role.customer {
    background: #EFF6FF;
    color: #1D4ED8;
}

.badge-role.admin {
    background: #FFF7ED;
    color: #C2410C;
}

/* Footer layout */
.db-footer-bar {
    background: #FFFFFF;
    border-top: 1px solid #E6E6E6;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: #9CA3AF;
    flex-shrink: 0;
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
                <li class="db-menu__item">
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
                <li class="db-menu__item active">
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
            <h1 class="db-topbar__title">Registered Users</h1>
            <a href="<?= BASE_PATH ?>/admin/dashboard.php" class="db-btn-secondary-sm" style="padding: 0.5rem 1rem;">
                ← Back to Dashboard
            </a>
        </header>

        <!-- Body Scroll Area -->
        <div class="db-body">

            <!-- Users Table Card -->
            <div class="db-panel-card" style="width:100%;">
                <div class="db-panel-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E6E6E6; padding-bottom:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                    <h2 class="db-panel-title" style="margin-bottom:0;">All Accounts</h2>
                    
                    <!-- Real-time User Search -->
                    <input type="text" id="user-search" onkeyup="filterUsersTable()" placeholder="Search Name, Email, or Org..." style="padding:0.4rem 0.75rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.8rem; outline:none; background:#FFF; min-width:220px;">
                </div>

                <div class="db-table-wrap">
                    <table class="db-table" id="db-users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Organization</th>
                                <th>Role</th>
                                <th>Registered Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:3rem; color:#9CA3AF;">
                                        No registered users in system database yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="user-row" 
                                        data-name="<?= htmlspecialchars(strtolower($u['name'])) ?>"
                                        data-email="<?= htmlspecialchars(strtolower($u['email'])) ?>"
                                        data-org="<?= htmlspecialchars(strtolower($u['organization'] ?? '')) ?>">
                                        
                                        <td><strong>#<?= $u['id'] ?></strong></td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                                <div style="width:28px; height:28px; border-radius:50%; background:#FF7A1A; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:0.75rem;">
                                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                                </div>
                                                <span style="font-weight:600; color:#1F2937;"><?= htmlspecialchars($u['name']) ?></span>
                                            </div>
                                        </td>
                                        <td><span style="color:#4B5563; font-weight: 500;"><?= htmlspecialchars($u['email']) ?></span></td>
                                        <td><span><?= !empty($u['phone']) ? htmlspecialchars($u['phone']) : '<span style="color:#9CA3AF; font-style:italic;">Not provided</span>' ?></span></td>
                                        <td><span><?= !empty($u['organization']) ? htmlspecialchars($u['organization']) : '<span style="color:#9CA3AF; font-style:italic;">None</span>' ?></span></td>
                                        <td>
                                            <span class="badge-role <?= htmlspecialchars(strtolower($u['role'])) ?>">
                                                <?= htmlspecialchars($u['role']) ?>
                                            </span>
                                        </td>
                                        <td><span style="color:#6B7280;"><?= date('Y-m-d H:i', strtotime($u['created_at'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
// Live table search filtering for users table
function filterUsersTable() {
    const searchVal = document.getElementById('user-search').value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const email = row.getAttribute('data-email');
        const org = row.getAttribute('data-org');
        
        if (name.includes(searchVal) || email.includes(searchVal) || org.includes(searchVal)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
