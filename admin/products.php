<?php
/**
 * admin/products.php — Admin Product Management
 * View, add, and delete food products.
 * PROTECTED: Admin role required.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin(); // 🛡️ Admin only

$pdo = get_db_connection();
$error = null;
$success = null;

// ── Handle Add/Edit Product ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $product_id = intval($_POST['id'] ?? 0);

        // Image upload handling
        $image_filename = null;
        $has_image = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK;

        if (empty($name)) {
            $error = 'Product name is required.';
        } elseif ($price <= 0) {
            $error = 'Please enter a valid price greater than 0.';
        } else {
            if ($has_image) {
                $file = $_FILES['image'];
                $max_size = 5 * 1024 * 1024; // 5MB
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                
                if ($file['size'] > $max_size) {
                    $error = 'The product image is too large. Maximum size is 5MB.';
                } elseif (!in_array($file['type'], $allowed_types)) {
                    $error = 'Invalid image type. Only JPG, PNG, and WEBP are allowed.';
                } else {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $image_filename = uniqid('product_', true) . '.' . $ext;
                    
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }
                    
                    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $image_filename)) {
                        $error = 'Failed to save product image.';
                        $image_filename = null;
                    }
                }
            }

            if ($error === null) {
                try {
                    if ($action === 'add') {
                        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (:name, :description, :price, :image)");
                        $stmt->execute([
                            ':name' => $name,
                            ':description' => $description,
                            ':price' => $price,
                            ':image' => $image_filename
                        ]);
                        $_SESSION['success'] = 'Product added successfully!';
                    } else {
                        // Edit action
                        if ($has_image) {
                            $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, image = :image WHERE id = :id");
                            $stmt->execute([
                                ':name' => $name,
                                ':description' => $description,
                                ':price' => $price,
                                ':image' => $image_filename,
                                ':id' => $product_id
                            ]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price WHERE id = :id");
                            $stmt->execute([
                                ':name' => $name,
                                ':description' => $description,
                                ':price' => $price,
                                ':id' => $product_id
                            ]);
                        }
                        $_SESSION['success'] = 'Product updated successfully!';
                    }
                    
                    // PRG Pattern: Redirect to prevent form resubmission
                    header("Location: " . BASE_PATH . "/admin/products.php");
                    exit;
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// ── Handle Delete Product ───────────────────────────────────────────────────
$delete_error = null;
$delete_success = null;
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $delete_id]);
        $_SESSION['delete_success'] = 'Product deleted successfully!';
        
        // Redirect to clean query parameter from URL and prevent repeat deletions on refresh
        header("Location: " . BASE_PATH . "/admin/products.php");
        exit;
    } catch (PDOException $e) {
        $delete_error = 'Database error: ' . $e->getMessage();
    }
}

// ── Load flash messages from session ─────────────────────────────────────────
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['delete_success'])) {
    $delete_success = $_SESSION['delete_success'];
    unset($_SESSION['delete_success']);
}

// ── Fetch all products ──────────────────────────────────────────────────────
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error loading products: " . $e->getMessage());
}

$page_title = 'Product Management';
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

/* Layout for Product page columns */
.prod-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    align-items: start;
}

/* Modal backdrop overlay with background blur */
.prod-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(11, 11, 11, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.prod-modal-backdrop.active {
    display: flex;
}

/* Modal Card Card Container */
.prod-modal-card {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #E6E6E6;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    width: 92%;
    max-width: 480px;
    padding: 2rem;
    position: relative;
    animation: prodModalScale 0.2s ease-out;
}

.prod-modal-close {
    position: absolute;
    top: 1.25rem;
    right: 1.25rem;
    background: none;
    border: none;
    font-size: 1.25rem;
    font-weight: 600;
    color: #9CA3AF;
    cursor: pointer;
    transition: color 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
}

.prod-modal-close:hover {
    color: #0B0B0B;
    background: #F3F4F6;
}

@keyframes prodModalScale {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* Add product form styling */
.prod-form-group {
    margin-bottom: 1.25rem;
}

.prod-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #0B0B0B;
    margin-bottom: 0.375rem;
}

.prod-input, .prod-textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    font-family: inherit;
    transition: all 0.15s ease;
}

.prod-input:focus, .prod-textarea:focus {
    border-color: #FF7A1A;
    box-shadow: 0 0 0 3px rgba(255, 122, 26, 0.1);
}

.prod-textarea {
    height: 100px;
    resize: none;
}

.prod-btn {
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

.prod-btn:hover {
    background: #E06600;
}

/* Custom Alert styling inside card */
.prod-alert {
    padding: 0.875rem 1.25rem;
    border-radius: 10px;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid;
    border-left-width: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    position: relative;
    animation: slideInAlert 0.2s ease-out;
}

.prod-alert--error {
    background: #FFF5F5;
    border-color: #FED7D7;
    border-left-color: #E53E3E;
    color: #C53030;
}

.prod-alert--success {
    background: #F0FDF4;
    border-color: #DCFCE7;
    border-left-color: #16A34A;
    color: #15803D;
}

.prod-alert-close {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: currentColor;
    opacity: 0.6;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.15s;
}

.prod-alert-close:hover {
    opacity: 1;
}

@keyframes slideInAlert {
    from { transform: translateY(-8px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Table styling */
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

.db-btn-danger-sm {
    background: #FEF2F2;
    border: 1px solid #FEE2E2;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #DC2626;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    transition: all 0.15s ease;
}

.db-btn-danger-sm:hover {
    background: #FEE2E2;
    border-color: #FCA5A5;
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
                <li class="db-menu__item">
                    <a href="<?= BASE_PATH ?>/admin/complaints.php">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        Complaints
                    </a>
                </li>
                <li class="db-menu__item active">
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
            <h1 class="db-topbar__title">Product Management</h1>
            <a href="<?= BASE_PATH ?>/admin/dashboard.php" class="db-btn-secondary-sm" style="padding: 0.5rem 1rem;">
                ← Back to Dashboard
            </a>
        </header>

        <!-- Body Scroll Area -->
        <div class="db-body">

            <?php if ($delete_error): ?>
                <div class="prod-alert prod-alert--error" style="margin-bottom:1.5rem; max-width:100%;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span><?= htmlspecialchars($delete_error) ?></span>
                    <button class="prod-alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($delete_success): ?>
                <div class="prod-alert prod-alert--success" style="margin-bottom:1.5rem; max-width:100%;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?= htmlspecialchars($delete_success) ?></span>
                    <button class="prod-alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="prod-alert prod-alert--success" style="margin-bottom:1.5rem; max-width:100%;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?= htmlspecialchars($success) ?></span>
                    <button class="prod-alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <div class="prod-layout" id="prod-layout-container">

                <!-- Existing Products Table Card -->
                <div class="db-panel-card" style="width:100%;">
                    <div class="db-panel-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E6E6E6; padding-bottom:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                        <h2 class="db-panel-title" style="margin-bottom:0;">Existing Products</h2>
                        
                        <!-- Search & Add Actions -->
                        <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                            <input type="text" id="prod-search" onkeyup="filterProductsTable()" placeholder="Search Name or ID..." style="padding:0.4rem 0.75rem; border:1px solid #D1D5DB; border-radius:6px; font-size:0.8rem; outline:none; background:#FFF; min-width:180px;">
                            <button class="db-btn-orange" id="toggle-add-btn" onclick="toggleAddProductForm(true)" style="font-size:0.8rem; padding:0.4rem 0.8rem; display:inline-flex; align-items:center; gap:0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Add Product
                            </button>
                        </div>
                    </div>

                    <div class="db-table-wrap">
                        <table class="db-table" id="db-products-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Added Date</th>
                                    <th>Last Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding:3rem; color:#9CA3AF;">
                                            No food products in menu database yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $p): ?>
                                        <tr class="product-row" 
                                            data-id="<?= $p['id'] ?>" 
                                            data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>">
                                            <td><strong>#<?= $p['id'] ?></strong></td>
                                            <td>
                                                <div style="width:40px; height:40px; border-radius:6px; background:#F3F4F6; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #E6E6E6;">
                                                    <?php if (!empty($p['image'])): ?>
                                                        <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($p['image']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                                    <?php else: ?>
                                                        <span style="font-size:1.25rem;">🍔</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><span style="font-weight:600; color:#0B0B0B;"><?= htmlspecialchars($p['name']) ?></span></td>
                                            <td><span style="color:#6B7280; font-size:0.8125rem;"><?= htmlspecialchars($p['description'] ?? 'No description') ?></span></td>
                                            <td><strong>LKR <?= number_format($p['price'], 2) ?></strong></td>
                                            <td><span style="font-size:0.8125rem; color:#6B7280;"><?= isset($p['created_at']) ? date('Y-m-d H:i', strtotime($p['created_at'])) : 'N/A' ?></span></td>
                                            <td><span style="font-size:0.8125rem; color:#6B7280;"><?= !empty($p['updated_at']) ? date('Y-m-d H:i', strtotime($p['updated_at'])) : (isset($p['created_at']) ? date('Y-m-d H:i', strtotime($p['created_at'])) : 'N/A') ?></span></td>
                                            <td>
                                                <button class="db-btn-secondary-sm" style="padding: 0.25rem 0.5rem; margin-right: 0.25rem;" onclick='editProduct(<?= json_encode([
                                                    "id" => $p['id'],
                                                    "name" => $p['name'],
                                                    "description" => $p['description'],
                                                    "price" => $p['price']
                                                ]) ?>)'>Edit</button>
                                                <a href="<?= BASE_PATH ?>/admin/products.php?delete=<?= $p['id'] ?>" class="db-btn-danger-sm" onclick="return confirm('Are you sure you want to delete this product? All complaints related to this product will be unlinked.')">
                                                    Delete
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

<!-- ─── MODAL ADD/EDIT PRODUCT OVERLAY with Blur ─── -->
<div class="prod-modal-backdrop" id="product-modal-overlay" onclick="closeModalOnBackdrop(event)">
    <div class="prod-modal-card">
        <!-- Close Button -->
        <button class="prod-modal-close" onclick="toggleAddProductForm(false)">&times;</button>
        
        <h2 class="db-panel-title" id="modal-title" style="margin-bottom:1.5rem; font-size:1.25rem; border-bottom:1px solid #E6E6E6; padding-bottom:0.75rem; color:#0B0B0B;">Add New Product</h2>
        
        <!-- Inside modal card, we only render validation errors -->
        <?php if ($error): ?>
            <div class="prod-alert prod-alert--error" style="margin-bottom:1.25rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <span><?= htmlspecialchars($error) ?></span>
                <button class="prod-alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/admin/products.php" enctype="multipart/form-data">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-product-id" value="">
            
            <div class="prod-form-group">
                <label class="prod-label" for="name">Product Name</label>
                <input class="prod-input" type="text" id="name" name="name" placeholder="e.g., Double Cheese Burger" required>
            </div>
            
            <div class="prod-form-group">
                <label class="prod-label" for="description">Description</label>
                <textarea class="prod-textarea" id="description" name="description" placeholder="Write a short description about this food item..."></textarea>
            </div>
            
            <div class="prod-form-group">
                <label class="prod-label" for="price">Price (LKR)</label>
                <input class="prod-input" type="number" id="price" name="price" step="0.01" min="0.01" placeholder="e.g., 990" required>
            </div>

            <div class="prod-form-group">
                <label class="prod-label" for="image">Product Image <span>(Optional)</span></label>
                <input class="prod-input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
            </div>
            
            <button class="prod-btn" id="modal-submit-btn" type="submit" style="margin-top:0.5rem;">Add Product</button>
        </form>
    </div>
</div>

<script>
function toggleAddProductForm(show) {
    const overlay = document.getElementById('product-modal-overlay');
    if (show) {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent main page scrolling when open
    } else {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset to default "Add Product" mode when closing
        setTimeout(() => {
            document.getElementById('form-action').value = 'add';
            document.getElementById('form-product-id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('price').value = '';
            document.getElementById('modal-title').innerText = 'Add New Product';
            document.getElementById('modal-submit-btn').innerText = 'Add Product';
        }, 200);
    }
}

// Function to trigger edit mode
function editProduct(productData) {
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-product-id').value = productData.id;
    document.getElementById('name').value = productData.name;
    document.getElementById('description').value = productData.description || '';
    document.getElementById('price').value = productData.price;
    
    document.getElementById('modal-title').innerText = 'Edit Product Details';
    document.getElementById('modal-submit-btn').innerText = 'Save Changes';
    
    // Open the modal
    toggleAddProductForm(true);
}

// Close when clicking overlay backdrop directly
function closeModalOnBackdrop(event) {
    if (event.target.id === 'product-modal-overlay') {
        toggleAddProductForm(false);
    }
}

// Live table search filtering for products table
function filterProductsTable() {
    const searchVal = document.getElementById('prod-search').value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const id = row.getAttribute('data-id').toLowerCase();
        const name = row.getAttribute('data-name').toLowerCase();
        
        if (id.includes(searchVal) || name.includes(searchVal)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Automatically reveal the form if there's a validation error
<?php if ($error): ?>
    document.addEventListener("DOMContentLoaded", function() {
        toggleAddProductForm(true);
    });
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
