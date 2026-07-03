<?php
/**
 * admin/dashboard.php — Admin Control Dashboard
 * Overview of all complaints with filter and search.
 * PROTECTED: Admin role required.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin(); // 🛡️ Admin only

$pdo = get_db_connection();

// ── Step 1: Query global stats across ALL users ──────────────────────────────
try {
    $stat_total = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
    $stat_new = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'new'")->fetchColumn();
    $stat_progress = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'in_progress'")->fetchColumn();
    $stat_resolved = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn();
} catch (PDOException $e) {
    die("Error loading admin stats: " . $e->getMessage());
}

// ── Step 2: Fetch all complaints joining users and products ─────────────────
try {
    $stmt = $pdo->query("
        SELECT c.id, c.category, c.priority, c.status, c.created_at, 
               u.name AS customer_name, p.name AS product_name
        FROM complaints c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN products p ON c.product_id = p.id
        ORDER BY c.created_at DESC
    ");
    $complaints = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error loading admin complaints: " . $e->getMessage());
}

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="page-wrapper">
    <div class="container animate-fade-in">

        <div class="page-header">
            <h1 class="page-header__title">🛡️ Admin Dashboard</h1>
            <p class="page-header__subtitle">Manage and resolve all customer complaints.</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid" style="margin-bottom:2.5rem;">
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--primary">📋</div>
                <div>
                    <div class="stat-card__value" id="admin-stat-total"><?= (int)$stat_total ?></div>
                    <div class="stat-card__label">Total</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--info">🆕</div>
                <div>
                    <div class="stat-card__value" id="admin-stat-new"><?= (int)$stat_new ?></div>
                    <div class="stat-card__label">New</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--warning">⏳</div>
                <div>
                    <div class="stat-card__value" id="admin-stat-progress"><?= (int)$stat_progress ?></div>
                    <div class="stat-card__label">In Progress</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--success">✅</div>
                <div>
                    <div class="stat-card__value" id="admin-stat-resolved"><?= (int)$stat_resolved ?></div>
                    <div class="stat-card__label">Resolved</div>
                </div>
            </div>
        </div>

        <!-- Actions Row -->
        <div class="flex-between" style="margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
            <h2 style="font-size:1.25rem; font-weight:700;">All Complaints</h2>
            <a href="<?= BASE_PATH ?>/admin/products.php" class="btn btn--secondary" id="admin-manage-products-btn">
                🍔 Manage Products
            </a>
        </div>

        <!-- Complaints Table -->
        <div class="table-wrapper">
            <table class="table" id="admin-complaints-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($complaints)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; color:var(--clr-text-muted); padding:3rem;">
                                No complaints submitted yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td>#<?= (int)$complaint['id'] ?></td>
                                <td><strong><?= htmlspecialchars($complaint['customer_name']) ?></strong></td>
                                <td><?= htmlspecialchars($complaint['product_name'] ?? 'Deleted Product') ?></td>
                                <td><?= htmlspecialchars($complaint['category']) ?></td>
                                <td>
                                    <?php if ($complaint['priority'] === 'High'): ?>
                                        <span style="color:var(--clr-danger); font-weight:600;">🔴 High</span>
                                    <?php elseif ($complaint['priority'] === 'Medium'): ?>
                                        <span style="color:var(--clr-warning); font-weight:600;">🟡 Medium</span>
                                    <?php else: ?>
                                        <span style="color:var(--clr-success); font-weight:600;">🟢 Low</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge--<?= $complaint['status'] === 'new' ? 'info' : ($complaint['status'] === 'in_progress' ? 'warning' : ($complaint['status'] === 'resolved' ? 'success' : 'secondary')) ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $complaint['status']))) ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($complaint['created_at'])) ?></td>
                                <td>
                                    <!-- Directs to a detail/process page -->
                                    <a href="<?= BASE_PATH ?>/admin/complaint_details.php?id=<?= (int)$complaint['id'] ?>" class="btn btn--secondary btn--sm">
                                        ⚙️ Process
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
