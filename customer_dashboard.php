<?php
/**
 * customer_dashboard.php — Customer Home Page
 * Shows the customer's complaint statistics and recent complaints.
 * PROTECTED: Requires login. Redirects to login.php if not authenticated.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login(); // 🛡️ Protect this page

$user_id = get_current_user_id();
$pdo = get_db_connection();

// ── Step 1: Query statistics for this customer ──────────────────────────────
// We can run counts for total and for each individual status.
try {
    // Total Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $stat_total = $stmt->fetchColumn();

    // New Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id AND status = 'new'");
    $stmt->execute([':user_id' => $user_id]);
    $stat_new = $stmt->fetchColumn();

    // In Progress Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id AND status = 'in_progress'");
    $stmt->execute([':user_id' => $user_id]);
    $stat_progress = $stmt->fetchColumn();

    // Resolved Complaints
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = :user_id AND status = 'resolved'");
    $stmt->execute([':user_id' => $user_id]);
    $stat_resolved = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Error loading statistics: " . $e->getMessage());
}

// ── Step 2: Fetch complaints list with product names ────────────────────────
// LEARNING NOTE: LEFT JOIN
//   We want to show the product name, but the name is stored in the 'products' table.
//   We use a LEFT JOIN to link the complaints table (c) to the products table (p)
//   using 'c.product_id = p.id'.
//   We use LEFT JOIN so that even if the product gets deleted (product_id becomes NULL),
//   the complaint still shows up in the list!
try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.category, c.priority, c.status, c.created_at, p.name AS product_name
        FROM complaints c
        LEFT JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :user_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $complaints = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error loading complaints: " . $e->getMessage());
}

$page_title = 'My Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page-wrapper">
    <div class="container animate-fade-in">

        <div class="page-header">
            <h1 class="page-header__title">
                👋 Welcome, <?= htmlspecialchars(get_current_user_name()) ?>
            </h1>
            <p class="page-header__subtitle">
                Here's a summary of your complaints and their current status.
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" style="margin-bottom:2.5rem;">
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--primary">📋</div>
                <div>
                    <div class="stat-card__value" id="stat-total"><?= (int)$stat_total ?></div>
                    <div class="stat-card__label">Total Complaints</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--info">🔵</div>
                <div>
                    <div class="stat-card__value" id="stat-new"><?= (int)$stat_new ?></div>
                    <div class="stat-card__label">New</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--warning">🟡</div>
                <div>
                    <div class="stat-card__value" id="stat-progress"><?= (int)$stat_progress ?></div>
                    <div class="stat-card__label">In Progress</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--success">✅</div>
                <div>
                    <div class="stat-card__value" id="stat-resolved"><?= (int)$stat_resolved ?></div>
                    <div class="stat-card__label">Resolved</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex" style="gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
            <a href="<?= BASE_PATH ?>/submit_complaint.php" class="btn btn--primary" id="dashboard-new-complaint-btn">
                ➕ Submit New Complaint
            </a>
            <a href="<?= BASE_PATH ?>/track_complaint.php" class="btn btn--secondary" id="dashboard-track-btn">
                📡 Track a Complaint
            </a>
        </div>

        <!-- Complaints Table -->
        <div class="card">
            <div class="flex-between" style="margin-bottom:1.5rem;">
                <h2 style="font-size:1.25rem; font-weight:700;">My Complaints</h2>
            </div>
            <div class="table-wrapper">
                <table class="table" id="complaints-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--clr-text-muted); padding:3rem;">
                                    No complaints yet. <a href="<?= BASE_PATH ?>/submit_complaint.php">Submit your first one!</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td>#<?= (int)$complaint['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($complaint['product_name'] ?? 'Deleted Product') ?></strong></td>
                                    <td><?= htmlspecialchars($complaint['category']) ?></td>
                                    <td>
                                        <!-- Styled priority tags -->
                                        <?php if ($complaint['priority'] === 'High'): ?>
                                            <span style="color:var(--clr-danger); font-weight:600;">🔴 High</span>
                                        <?php elseif ($complaint['priority'] === 'Medium'): ?>
                                            <span style="color:var(--clr-warning); font-weight:600;">🟡 Medium</span>
                                        <?php else: ?>
                                            <span style="color:var(--clr-success); font-weight:600;">🟢 Low</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Styled status tags -->
                                        <span class="badge badge--<?= $complaint['status'] === 'new' ? 'info' : ($complaint['status'] === 'in_progress' ? 'warning' : ($complaint['status'] === 'resolved' ? 'success' : 'secondary')) ?>">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $complaint['status']))) ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($complaint['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_PATH ?>/track_complaint.php?id=<?= (int)$complaint['id'] ?>" class="btn btn--secondary btn--sm">
                                            📡 Track
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
