<?php
/**
 * admin/complaint_details.php — Admin Complaint Process Page
 * View full complaint details, add notes, and change status.
 * PROTECTED: Admin role required.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin(); // 🛡️ Admin only

$pdo = get_db_connection();
$error = null;
$success = null;

// Get complaint ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ── Handle Note or Status Update ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    $status = trim($_POST['status'] ?? '');
    $admin_note = trim($_POST['admin_note'] ?? '');

    try {
        $stmt = $pdo->prepare("
            UPDATE complaints 
            SET status = :status, admin_note = :admin_note 
            WHERE id = :id
        ");
        $stmt->execute([
            ':status' => $status,
            ':admin_note' => $admin_note,
            ':id' => $id
        ]);
        $success = 'Complaint details updated successfully!';
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// ── Fetch complaint details ──────────────────────────────────────────────────
$complaint = null;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   u.name AS customer_name, u.email AS customer_email,
                   u.phone AS customer_phone, u.organization AS customer_organization,
                   p.name AS product_name
            FROM complaints c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN products p ON c.product_id = p.id
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $complaint = $stmt->fetch();
    } catch (PDOException $e) {
        die("Error loading complaint: " . $e->getMessage());
    }
}

if (!$complaint) {
    die("Complaint not found.");
}

$page_title = "Process Complaint #" . str_pad($id, 4, '0', STR_PAD_LEFT);
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

.db-panel-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0B0B0B;
    margin-bottom: 1.25rem;
}

/* Detailed page columns layout */
.details-layout {
    display: grid;
    grid-template-columns: 2fr 1.25fr;
    gap: 1.5rem;
    align-items: start;
}

/* Info groups */
.info-section {
    margin-bottom: 1.5rem;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.375rem;
}

.info-value {
    font-size: 0.95rem;
    color: #0B0B0B;
    font-weight: 500;
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

/* Photo upload preview */
.complaint-img-wrap {
    margin-top: 0.75rem;
    border-radius: 8px;
    overflow: hidden;
    max-width: 100%;
    border: 1px solid #E6E6E6;
}

.complaint-img {
    display: block;
    width: 100%;
    max-height: 350px;
    object-fit: contain;
    background: #F9FAFB;
}

/* Update panel forms */
.update-form-group {
    margin-bottom: 1.25rem;
}

.update-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #0B0B0B;
    margin-bottom: 0.375rem;
}

.update-select, .update-textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    font-family: inherit;
    transition: all 0.15s ease;
}

.update-select:focus, .update-textarea:focus {
    border-color: #FF7A1A;
    box-shadow: 0 0 0 3px rgba(255, 122, 26, 0.1);
}

.update-textarea {
    height: 120px;
    resize: none;
}

.update-btn {
    background: #FF7A1A;
    color: #FFFFFF;
    border: none;
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
}

.update-btn:hover {
    background: #E06600;
}

/* Custom Alert styling inside card */
.prod-alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
}

.prod-alert--error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #DC2626;
}

.prod-alert--success {
    background: #ECFDF5;
    border: 1px solid #A7F3D0;
    color: #059669;
}

/* Custom Footer bar at the end */
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
            <h1 class="db-topbar__title">Complaint Processing</h1>
            <a href="<?= BASE_PATH ?>/admin/dashboard.php" class="db-btn-secondary-sm" style="padding: 0.5rem 1rem;">
                ← Back to Dashboard
            </a>
        </header>

        <!-- Body Scroll Area -->
        <div class="db-body">

            <div class="details-layout">

                <!-- Left Column: Details -->
                <div class="db-panel-card">
                    <h2 class="db-panel-title" style="border-bottom:1px solid #E6E6E6; padding-bottom:0.75rem;">Complaint #<?= str_pad($complaint['id'], 4, '0', STR_PAD_LEFT) ?></h2>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:1.5rem;">
                        <div class="info-section">
                            <div class="info-label">Customer Name</div>
                            <div class="info-value"><?= htmlspecialchars($complaint['customer_name']) ?></div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">Customer Email</div>
                            <div class="info-value"><?= htmlspecialchars($complaint['customer_email']) ?></div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">Category</div>
                            <div class="info-value"><?= htmlspecialchars($complaint['category']) ?></div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">Food Item / Product</div>
                            <div class="info-value"><?= htmlspecialchars($complaint['product_name'] ?? 'General / Other') ?></div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">Priority</div>
                            <div class="info-value">
                                <span class="priority-dot <?= strtolower($complaint['priority']) ?>">
                                    <?= htmlspecialchars($complaint['priority']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-section">
                            <div class="info-label">Submitted On</div>
                            <div class="info-value"><?= date('F j, Y, g:i a', strtotime($complaint['created_at'])) ?></div>
                        </div>
                        <?php if (!empty($complaint['customer_phone'])): ?>
                            <div class="info-section">
                                <div class="info-label">Customer Phone</div>
                                <div class="info-value"><?= htmlspecialchars($complaint['customer_phone']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($complaint['customer_organization'])): ?>
                            <div class="info-section">
                                <div class="info-label">Organization</div>
                                <div class="info-value"><?= htmlspecialchars($complaint['customer_organization']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-section" style="margin-top:1rem;">
                        <div class="info-label">Description of Issue</div>
                        <div class="info-value" style="
                            background: #F9FAFB;
                            border: 1px solid #E6E6E6;
                            border-radius: 8px;
                            padding: 1rem;
                            white-space: pre-wrap;
                            line-height: 1.5;
                        "><?= htmlspecialchars($complaint['description']) ?></div>
                    </div>

                    <?php if (!empty($complaint['photo'])): ?>
                        <div class="info-section">
                            <div class="info-label">Attachment Image</div>
                            <div class="complaint-img-wrap">
                                <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($complaint['photo']) ?>" class="complaint-img" alt="Attached evidence photo">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Process/Update Form -->
                <div class="db-panel-card">
                    <h2 class="db-panel-title" style="border-bottom:1px solid #E6E6E6; padding-bottom:0.75rem;">Action Panel</h2>
                    
                    <?php if ($error): ?>
                        <div class="prod-alert prod-alert--error">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="prod-alert prod-alert--success">✅ <?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_PATH ?>/admin/complaint_details.php?id=<?= $complaint['id'] ?>" style="margin-top:1.5rem;">
                        
                        <div class="update-form-group">
                            <label class="update-label" for="status">Complaint Status</label>
                            <select class="update-select" id="status" name="status">
                                <option value="new" <?= $complaint['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                <option value="in_progress" <?= $complaint['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="resolved" <?= $complaint['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                <option value="closed" <?= $complaint['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="update-form-group">
                            <label class="update-label" for="admin_note">Admin Action Notes</label>
                            <textarea class="update-textarea" id="admin_note" name="admin_note" placeholder="Write internal response note, action details, or notification messages here..."><?= htmlspecialchars($complaint['admin_note'] ?? '') ?></textarea>
                        </div>
                        
                        <button class="update-btn" type="submit">Update Complaint</button>
                    </form>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
