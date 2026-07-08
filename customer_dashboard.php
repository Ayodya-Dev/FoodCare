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
        SELECT c.id, c.category, c.priority, c.status, c.created_at, c.updated_at, p.name AS product_name
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

<style>
/* Quick action cards */
.user-quick-actions {
    margin-top: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}
.quick-card {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 16px;
    padding: 2.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    min-height: 220px;
}
.quick-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
}
.quick-card.submit-card:hover {
    border-color: #FF7A1A;
}
.quick-card.track-card:hover {
    border-color: #3B82F6;
}
.quick-card-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #FFF0E6;
    color: #FF7A1A;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
    transition: transform 0.25s ease;
}
.quick-card:hover .quick-card-circle {
    transform: scale(1.1);
}
.quick-card-circle.circle-blue {
    background: #EFF6FF;
    color: #3B82F6;
}
.quick-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}
.quick-card-desc {
    font-size: 0.75rem;
    color: #9CA3AF;
    margin: 0;
    line-height: 1.4;
}
.quick-card.info-card {
    align-items: stretch;
    justify-content: flex-start;
    text-align: left;
    padding: 1.5rem;
    cursor: default;
}
.quick-card.info-card:hover {
    transform: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
    border-color: #E5E7EB;
}
</style>

<main class="page-wrapper" style="background: #FAFBFB;">
    <div class="container animate-fade-in">

        <div class="page-header" style="margin-bottom: 2rem;">
            <h1 class="page-header__title" style="font-size: 2.25rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                Welcome, <?= htmlspecialchars(get_current_user_name()) ?>
            </h1>
            <p class="page-header__subtitle" style="font-size: 1.1rem; color: #6B7280; margin: 0;">
                Submit new feedback or track outstanding updates.
            </p>
        </div>

        <!-- Quick Action Cards Grid -->
        <div class="user-quick-actions">
            
            <!-- Card 1: Submit New Issue -->
            <a href="<?= BASE_PATH ?>/submit_complaint.php" class="quick-card submit-card" style="text-decoration:none;">
                <div class="quick-card-circle">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </div>
                <h3 class="quick-card-title">Submit New Issue</h3>
                <p class="quick-card-desc">Report food quality, delivery, or packaging issues</p>
            </a>

            <!-- Card 2: Track Complaint -->
            <a href="<?= BASE_PATH ?>/track_complaint.php" class="quick-card track-card" style="text-decoration:none;">
                <div class="quick-card-circle circle-blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <h3 class="quick-card-title">Track Complaint</h3>
                <p class="quick-card-desc">Search details and process logs of filed complaints</p>
            </a>

            <!-- Card 3: Activity Summary -->
            <div class="quick-card info-card">
                <h3 class="quick-card-title" style="margin-top:0.25rem; font-size:1.1rem; border-bottom: 1px solid #F3F4F6; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">Activity Summary</h3>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); text-align: center; gap: 0.5rem;">
                    <div>
                        <div style="font-size:1.6rem; font-weight:800; color:#111827;"><?= (int)$stat_total ?></div>
                        <div style="font-size:0.65rem; color:#9CA3AF; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-top:0.25rem;">Filed</div>
                    </div>
                    <div>
                        <div style="font-size:1.6rem; font-weight:800; color:#FF7A1A;"><?= (int)$stat_new + (int)$stat_progress ?></div>
                        <div style="font-size:0.65rem; color:#9CA3AF; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-top:0.25rem;">Active</div>
                    </div>
                    <div>
                        <div style="font-size:1.6rem; font-weight:800; color:#10B981;"><?= (int)$stat_resolved ?></div>
                        <div style="font-size:0.65rem; color:#9CA3AF; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-top:0.25rem;">Resolved</div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Complaints Table -->
        <div class="card">
            <div class="flex-between" style="margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                <h2 style="font-size:1.25rem; font-weight:700; margin:0;">My Complaints</h2>
                
                <!-- Filter bar controls -->
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
                    <select id="filter-category" onchange="filterUserComplaints()" style="padding:0.4rem 0.75rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.85rem; outline:none; background:#FFF; cursor:pointer;">
                        <option value="all">All Categories</option>
                        <option value="food quality">Food Quality</option>
                        <option value="packaging">Packaging</option>
                        <option value="delivery">Delivery</option>
                        <option value="hygiene">Hygiene</option>
                        <option value="other">Other</option>
                    </select>

                    <select id="filter-status" onchange="filterUserComplaints()" style="padding:0.4rem 0.75rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.85rem; outline:none; background:#FFF; cursor:pointer;">
                        <option value="all">All Statuses</option>
                        <option value="new">New</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>

                    <input type="date" id="filter-date" onchange="filterUserComplaints()" style="padding:0.35rem 0.5rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.85rem; outline:none; background:#FFF; cursor:pointer;">
                    
                    <button onclick="resetUserFilters()" style="padding:0.4rem 0.75rem; border:1px solid #D1D5DB; background:#F3F4F6; border-radius:6px; font-size:0.85rem; cursor:pointer; color:#4B5563; font-weight:500; transition:all 0.15s;">
                        Reset
                    </button>
                </div>
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
                            <th>Submitted Date</th>
                            <th>Last Updated</th>
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
                                <tr class="user-complaint-row"
                                    data-category="<?= htmlspecialchars(strtolower($complaint['category'])) ?>"
                                    data-status="<?= htmlspecialchars(strtolower($complaint['status'])) ?>"
                                    data-subdate="<?= date('Y-m-d', strtotime($complaint['created_at'])) ?>"
                                    data-upddate="<?= date('Y-m-d', strtotime($complaint['updated_at'])) ?>">
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
                                    <td><?= date('Y-m-d H:i', strtotime($complaint['updated_at'])) ?></td>
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

<script>
function filterUserComplaints() {
    const selCat = document.getElementById('filter-category').value.toLowerCase();
    const selStatus = document.getElementById('filter-status').value.toLowerCase();
    const selDate = document.getElementById('filter-date').value; // YYYY-MM-DD
    
    const rows = document.querySelectorAll('.user-complaint-row');
    
    rows.forEach(row => {
        const cat = row.getAttribute('data-category');
        const status = row.getAttribute('data-status');
        const subDate = row.getAttribute('data-subdate'); // YYYY-MM-DD
        const updDate = row.getAttribute('data-upddate'); // YYYY-MM-DD
        
        let matchCat = (selCat === 'all' || cat === selCat);
        let matchStatus = (selStatus === 'all' || status === selStatus);
        
        // Match if selected date matches either submitted date OR updated date
        let matchDate = true;
        if (selDate) {
            matchDate = (subDate === selDate || updDate === selDate);
        }
        
        if (matchCat && matchStatus && matchDate) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function resetUserFilters() {
    document.getElementById('filter-category').value = 'all';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date').value = '';
    filterUserComplaints();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
