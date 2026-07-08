<?php
/**
 * submit_complaint.php — Complaint Submission Form
 * Allows logged-in customers to file a food complaint.
 * PROTECTED: Requires login.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$error = null;
$pdo = get_db_connection();

// =============================================================================
// HANDLE FORM SUBMISSION (POST)
// =============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id  = trim($_POST['product_id'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $priority    = trim($_POST['priority'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // File upload configuration
    $uploaded_filename = null;
    $has_file = isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK;

    // ── Step 1: Basic validation ──────────────────────────────────────────────
    if (empty($product_id)) {
        $error = 'Please select the product you had an issue with.';
    } elseif (empty($category) || empty($priority) || empty($description)) {
        $error = 'All fields marked with an asterisk (*) are required.';
    } else {
        
        // ── Step 2: Handle File Upload (Optional) ─────────────────────────────
        if ($has_file) {
            $file = $_FILES['photo'];
            
            // Validate file size (5MB max)
            // 5 * 1024 * 1024 = 5242880 bytes
            $max_size = 5 * 1024 * 1024;
            
            // Validate allowed extensions/types
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            
            if ($file['size'] > $max_size) {
                $error = 'The photo is too large. Maximum size allowed is 5MB.';
            } elseif (!in_array($file['type'], $allowed_types)) {
                $error = 'Invalid file type. Only JPG, PNG, and WEBP images are allowed.';
            } else {
                // Securely name the file to prevent overwriting existing files
                // pathinfo() gets the file extension (e.g., .jpg)
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uploaded_filename = uniqid('complaint_', true) . '.' . $ext;
                
                // Create the uploads/ directory if it doesn't exist
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                
                // Move the temporary file to our uploads folder
                $destination = UPLOAD_DIR . $uploaded_filename;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $error = 'Failed to save the uploaded image. Please try again.';
                    $uploaded_filename = null; // Reset
                }
            }
        }

        // ── Step 3: Insert into the Database ──────────────────────────────────
        if ($error === null) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO complaints (user_id, product_id, category, priority, description, photo, status)
                    VALUES (:user_id, :product_id, :category, :priority, :description, :photo, 'new')
                ");
                $stmt->execute([
                    ':user_id'     => get_current_user_id(),
                    ':product_id'  => $product_id,
                    ':category'    => $category,
                    ':priority'    => $priority,
                    ':description' => $description,
                    ':photo'       => $uploaded_filename
                ]);

                // Success! Set a flash message and go to dashboard
                set_flash('success', 'Complaint submitted successfully! We are looking into it. 📡');
                redirect('/customer_dashboard.php');

            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ── Retrieve products from DB for the grid selection ────────────────────────
$products_stmt = $pdo->query("SELECT id, name, description, price, image FROM products ORDER BY name ASC");
$products = $products_stmt->fetchAll();

$page_title = 'Submit a Complaint';
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Force correct page layout — overrides any cached CSS */
html { height: auto !important; }
body { height: auto !important; display: flex; flex-direction: column; min-height: 100vh; }
body > main, body > .page-wrapper { flex: 1 1 auto; height: auto !important; overflow: visible !important; }
.site-footer { margin-top: 0 !important; }
</style>

<main class="page-wrapper">
    <div class="container animate-fade-in" style="max-width:800px;">

        <div class="page-header">
            <h1 class="page-header__title">📝 Submit a Complaint</h1>
            <p class="page-header__subtitle">
                Fill in the details below. Our team will review and respond as soon as possible.
            </p>
        </div>

        <form method="POST" action="<?= BASE_PATH ?>/submit_complaint.php" enctype="multipart/form-data" id="complaint-form" novalidate>

            <?php if ($error): ?>
            <div class="alert alert--error" style="margin-bottom:1.5rem;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Step 1: Select Product -->
            <div class="card" style="margin-bottom:1.5rem;">
                <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1.5rem;">1. Select the Product</h2>
                
                <div class="products-grid" id="product-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
                    <?php if (empty($products)): ?>
                        <p style="color:var(--clr-text-muted); font-size:0.875rem;">No products available.</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <!-- product-card JS triggers selection styling -->
                            <div class="product-card" data-product-id="<?= $product['id'] ?>" type="button" style="display:flex; flex-direction:column; align-items:center; text-align:center; padding:1.25rem; border:1px solid var(--fc-border); border-radius:12px; cursor:pointer; background:#fff; transition:all 0.15s ease;">
                                <div style="width:100%; height:90px; border-radius:8px; background:#F9FAFB; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #E6E6E6; margin-bottom:0.75rem;">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($product['image']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <span style="font-size:2.5rem;">🍔</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-card__name" style="font-weight:700; color:var(--fc-black); margin-bottom:0.25rem; font-size:0.95rem;"><?= htmlspecialchars($product['name']) ?></div>
                                <p style="color:var(--clr-text-muted); font-size:0.75rem; margin-top:0.25rem; line-height:1.4;">
                                    <?= htmlspecialchars($product['description']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Hidden input stores the selected product ID -->
                <input type="hidden" name="product_id" id="selected_product_id" value="">
            </div>

            <!-- Step 2: Complaint Details -->
            <div class="card" style="margin-bottom:1.5rem;">
                <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1.5rem;">2. Describe the Issue</h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label for="category" class="form-label">Category <span class="required">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">— Select Category —</option>
                            <option value="Food Quality">Food Quality</option>
                            <option value="Packaging">Packaging</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Hygiene">Hygiene</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority <span class="required">*</span></label>
                        <select id="priority" name="priority" class="form-select" required>
                            <option value="">— Select Priority —</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" class="form-textarea" rows="5"
                               placeholder="Describe the issue in detail. What happened? When did it happen? What was wrong?" required></textarea>
                </div>
            </div>

            <!-- Step 3: Upload Photo -->
            <div class="card" style="margin-bottom:2rem;">
                <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:1.5rem;">3. Add a Photo (Optional)</h2>
                <div class="upload-zone" id="photo-upload-zone">
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp">
                    <div class="upload-zone__icon">📷</div>
                    <p class="upload-zone__text">Click to upload or drag & drop a photo<br>
                        <small style="color:var(--clr-text-faint);">JPG, PNG, WEBP · Max 5MB</small>
                    </p>
                </div>
            </div>

            <div class="flex" style="gap:1rem; justify-content:flex-end; flex-wrap:wrap;">
                <a href="<?= BASE_PATH ?>/customer_dashboard.php" class="btn btn--secondary" id="submit-cancel-btn">Cancel</a>
                <button type="submit" class="btn btn--primary btn--lg" id="submit-complaint-btn">
                    🚀 Submit Complaint
                </button>
            </div>

        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
