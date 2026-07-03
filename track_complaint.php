<?php
/**
 * track_complaint.php — Complaint Tracking Page
 * Shows a visual progress timeline for a specific complaint.
 * PROTECTED: Requires login.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$page_title = 'Track Complaint';
require_once __DIR__ . '/includes/header.php';

$complaint = null;
$error = null;
$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($complaint_id > 0) {
    $pdo = get_db_connection();
    
    try {
        // Query the database for the complaint, joining with products to get product name.
        // We add "WHERE c.id = :id AND c.user_id = :user_id" so users cannot view other people's complaints!
        $stmt = $pdo->prepare("
            SELECT c.*, p.name AS product_name
            FROM complaints c
            LEFT JOIN products p ON c.product_id = p.id
            WHERE c.id = :id AND c.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $complaint_id,
            ':user_id' => get_current_user_id()
        ]);
        $complaint = $stmt->fetch();
        
        if (!$complaint) {
            $error = "Complaint #$complaint_id not found or you do not have permission to view it.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<main class="page-wrapper">
    <div class="container animate-fade-in" style="max-width:700px;">

        <div class="page-header">
            <h1 class="page-header__title">📡 Track Your Complaint</h1>
            <p class="page-header__subtitle">
                Enter your complaint ID to see the current status and any admin responses.
            </p>
        </div>

        <!-- Complaint lookup form -->
        <div class="card" style="margin-bottom:2rem;">
            <form method="GET" action="<?= BASE_PATH ?>/track_complaint.php" id="track-form">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="complaint_id" class="form-label">Complaint ID</label>
                    <div class="flex" style="gap:1rem;">
                        <input type="number" id="complaint_id" name="id" class="form-input"
                               placeholder="e.g. 42" min="1" value="<?= $complaint_id > 0 ? $complaint_id : '' ?>">
                        <button type="submit" class="btn btn--primary" id="track-search-btn" style="white-space:nowrap;">
                            🔍 Track
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="alert alert--error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Dynamic Timeline -->
        <?php if ($complaint): ?>
        
        <!-- Complaint Summary Card -->
        <div class="card" style="margin-bottom:1.5rem;">
            <h3 style="font-weight:700; margin-bottom:1rem;">Complaint Details</h3>
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <tr>
                    <td style="padding:0.5rem 0; color:var(--clr-text-muted);">Product:</td>
                    <td style="padding:0.5rem 0; font-weight:600;"><?= htmlspecialchars($complaint['product_name'] ?? 'Deleted Product') ?></td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0; color:var(--clr-text-muted);">Category:</td>
                    <td style="padding:0.5rem 0; font-weight:600;"><?= htmlspecialchars($complaint['category']) ?></td>
                </tr>
                <tr>
                    <td style="padding:0.5rem 0; color:var(--clr-text-muted);">Description:</td>
                    <td style="padding:0.5rem 0; color:var(--clr-text-muted); line-height:1.4;"><?= nl2br(htmlspecialchars($complaint['description'])) ?></td>
                </tr>
                <?php if (!empty($complaint['photo'])): ?>
                <tr>
                    <td style="padding:0.5rem 0; color:var(--clr-text-muted);">Attached Photo:</td>
                    <td style="padding:0.5rem 0;">
                        <!-- Display thumbnail of uploaded image -->
                        <a href="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($complaint['photo']) ?>" target="_blank">
                            <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($complaint['photo']) ?>" 
                                 alt="Complaint Photo" 
                                 style="max-width:180px; border-radius:8px; border:1px solid var(--clr-border); margin-top:0.5rem; display:block;">
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="card" id="complaint-timeline">
            <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1.5rem;">
                Complaint #<?= (int)$complaint['id'] ?> — Live Timeline
            </h2>
            
            <?php
            // Determine active step based on status
            // status ENUM: 'new', 'in_progress', 'resolved', 'closed'
            $status = $complaint['status'];
            ?>
            <div class="timeline">
                
                <!-- Step 1: Submitted -->
                <!-- Always complete since the row exists -->
                <div class="timeline-item timeline-item--done">
                    <div class="timeline-item__dot"></div>
                    <div>
                        <strong>Submitted</strong>
                        <p style="font-size:0.875rem; color:var(--clr-text-muted);">
                            Complaint received by FoodCare on <?= date('Y-m-d H:i', strtotime($complaint['created_at'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Step 2: Under Review -->
                <!-- Done if status is in_progress, resolved, or closed. Active if new. -->
                <?php 
                $step2_class = '';
                if ($status === 'new') $step2_class = 'timeline-item--active';
                elseif (in_array($status, ['in_progress', 'resolved', 'closed'])) $step2_class = 'timeline-item--done';
                ?>
                <div class="timeline-item <?= $step2_class ?>">
                    <div class="timeline-item__dot"></div>
                    <div>
                        <strong>Under Review</strong>
                        <p style="font-size:0.875rem; color:var(--clr-text-muted);">
                            Our kitchen team is investigating the issue.
                        </p>
                    </div>
                </div>

                <!-- Step 3: Resolved -->
                <!-- Done if status is resolved or closed. Active if in_progress. -->
                <?php 
                $step3_class = '';
                if ($status === 'in_progress') $step3_class = 'timeline-item--active';
                elseif (in_array($status, ['resolved', 'closed'])) $step3_class = 'timeline-item--done';
                ?>
                <div class="timeline-item <?= $step3_class ?>">
                    <div class="timeline-item__dot"></div>
                    <div>
                        <strong>Resolved</strong>
                        <?php if (in_array($status, ['resolved', 'closed'])): ?>
                            <p style="font-size:0.875rem; color:var(--clr-text-muted);">
                                Issue has been resolved by our admin.
                            </p>
                        <?php else: ?>
                            <p style="font-size:0.875rem; color:var(--clr-text-faint);">Waiting...</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Step 4: Closed -->
                <!-- Active if status is closed. -->
                <?php 
                $step4_class = '';
                if ($status === 'closed') $step4_class = 'timeline-item--active timeline-item--done';
                ?>
                <div class="timeline-item <?= $step4_class ?>">
                    <div class="timeline-item__dot"></div>
                    <div>
                        <strong>Closed</strong>
                        <?php if ($status === 'closed'): ?>
                            <p style="font-size:0.875rem; color:var(--clr-text-muted);">
                                Complaint marked as closed. Thank you for your feedback!
                            </p>
                        <?php else: ?>
                            <p style="font-size:0.875rem; color:var(--clr-text-faint);">Waiting...</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Admin Note Section -->
            <?php if (!empty($complaint['admin_note'])): ?>
            <div style="margin-top:2rem; padding:1rem; background:rgba(249,115,22,0.08); border-left:4px solid var(--clr-primary); border-radius:4px;">
                <strong style="display:block; margin-bottom:0.5rem; color:var(--clr-primary);">📢 Admin Resolution Note:</strong>
                <p style="font-size:0.9rem; line-height:1.4; color:var(--clr-text-muted); margin:0;">
                    <?= nl2br(htmlspecialchars($complaint['admin_note'])) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
