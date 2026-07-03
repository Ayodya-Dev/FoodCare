<?php
/**
 * header.php — Shared Page Header Partial
 * ==========================================
 * This file is included at the TOP of every page with:
 *   require_once __DIR__ . '/includes/header.php';  (from root pages)
 *   require_once __DIR__ . '/../includes/header.php'; (from admin/ pages)
 *
 * It outputs:
 *   - The full <!DOCTYPE html> ... <body> opening
 *   - The <head> block with meta tags, CSS links
 *   - The fixed navigation bar
 *   - Any flash message alert
 *
 * Variables you can set BEFORE including this file:
 *   $page_title   — used in <title> tag (default: APP_NAME)
 *   $page_class   — added to <body> for page-specific styles (optional)
 */

// Load config if not already loaded (in case header.php is included directly)
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}
// Load auth helpers if not already loaded
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/auth.php';
}

// Set default page title
$page_title  = $page_title  ?? APP_NAME;
$page_class  = $page_class  ?? '';
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

// Get flash message (clears it from session after reading)
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FoodCare — BiteCraft Kitchen's official complaint and feedback portal. Submit, track, and manage food quality complaints easily.">
    <title><?= htmlspecialchars($page_title) ?> — <?= APP_NAME ?></title>

    <!-- Favicon (emoji fallback) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍔</text></svg>">

    <!-- Google Fonts (loaded via CSS @import in style.css) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Global Design System CSS -->
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/style.css">

    <!-- Page-specific CSS slot (define $extra_css before including header.php) -->
    <?php if (!empty($extra_css)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($extra_css) ?>">
    <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($page_class) ?>">

<!-- ─── Navigation Bar ─────────────────────────────────────────────────── -->
<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container navbar__inner">

        <!-- Brand / Logo -->
        <a href="<?= BASE_PATH ?>/index.php" class="navbar__brand" id="nav-brand">
            <div class="navbar__logo" aria-hidden="true">🍔</div>
            <span class="navbar__name"><?= APP_NAME ?></span>
        </a>

        <!-- Main Navigation Links -->
        <ul class="navbar__links" id="nav-links" role="list">
            <?php if (!is_logged_in()): ?>
                <!-- Public links (not logged in) -->
                <li><a href="<?= BASE_PATH ?>/index.php"   class="navbar__link" id="nav-home">Home</a></li>
                <li><a href="<?= BASE_PATH ?>/login.php"   class="navbar__link" id="nav-login">Login</a></li>
                <li><a href="<?= BASE_PATH ?>/register.php" class="navbar__link" id="nav-register">Register</a></li>

            <?php elseif (is_admin()): ?>
                <!-- Admin links -->
                <li><a href="<?= BASE_PATH ?>/admin/dashboard.php" class="navbar__link" id="nav-admin-dash">Dashboard</a></li>
                <li><a href="<?= BASE_PATH ?>/admin/products.php"  class="navbar__link" id="nav-admin-products">Products</a></li>

            <?php else: ?>
                <!-- Customer links -->
                <li><a href="<?= BASE_PATH ?>/customer_dashboard.php" class="navbar__link" id="nav-customer-dash">Dashboard</a></li>
                <li><a href="<?= BASE_PATH ?>/submit_complaint.php"    class="navbar__link" id="nav-submit">New Complaint</a></li>
                <li><a href="<?= BASE_PATH ?>/track_complaint.php"     class="navbar__link" id="nav-track">Track</a></li>
            <?php endif; ?>
        </ul>

        <!-- Right-side Actions -->
        <div class="navbar__actions">
            <?php if (is_logged_in()): ?>
                <div class="navbar__user">
                    <div class="navbar__avatar" aria-hidden="true">
                        <?= strtoupper(substr(get_current_user_name(), 0, 1)) ?>
                    </div>
                    <span id="nav-username"><?= htmlspecialchars(get_current_user_name()) ?></span>
                </div>
                <a href="<?= BASE_PATH ?>/logout.php" class="btn btn--secondary btn--sm" id="nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_PATH ?>/login.php"    class="btn btn--ghost btn--sm"   id="nav-login-btn">Login</a>
                <a href="<?= BASE_PATH ?>/register.php" class="btn btn--primary btn--sm" id="nav-register-btn">Get Started</a>
            <?php endif; ?>

            <!-- Mobile hamburger button -->
            <button class="navbar__toggle" id="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

    </div>
</nav>
<!-- ─── End Navbar ─────────────────────────────────────────────────────── -->

<!-- ─── Flash Message ──────────────────────────────────────────────────── -->
<?php if ($flash): ?>
<div class="container" style="padding-top: calc(var(--navbar-h) + 1rem);">
    <div class="alert alert--<?= htmlspecialchars($flash['type']) ?>" role="alert" id="flash-message">
        <span><?= htmlspecialchars($flash['message']) ?></span>
        <button class="alert__close" aria-label="Close" title="Dismiss">✕</button>
    </div>
</div>
<?php endif; ?>
<!-- ─── End Flash Message ──────────────────────────────────────────────── -->
