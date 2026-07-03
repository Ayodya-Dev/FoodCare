<?php
/**
 * index.php — Public Landing Page
 * Landing page for FoodCare (BiteCraft Kitchen).
 * Shows the brand, hero section, and call to action.
 */
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to correct dashboard
if (is_logged_in()) {
    redirect(is_admin() ? '/admin/dashboard.php' : '/customer_dashboard.php');
}

$page_title = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page-wrapper">
    <div class="container">

        <!-- Hero Section -->
        <section style="text-align:center; padding: 4rem 0 3rem;">
            <div style="font-size:3.5rem; margin-bottom:1rem;">🍔</div>
            <h1 style="font-size:clamp(2rem,5vw,3.5rem); font-weight:800; background:linear-gradient(135deg,#f97316,#8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:1rem;">
                BiteCraft Kitchen
            </h1>
            <p style="font-size:1.25rem; color:var(--clr-text-muted); max-width:520px; margin:0 auto 2.5rem;">
                Your voice matters. Submit, track, and resolve food complaints — fast and transparently.
            </p>
            <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
                <a href="<?= BASE_PATH ?>/register.php" class="btn btn--primary btn--lg" id="hero-register-btn">
                    🚀 Get Started — It's Free
                </a>
                <a href="<?= BASE_PATH ?>/login.php" class="btn btn--secondary btn--lg" id="hero-login-btn">
                    Login to Your Account
                </a>
            </div>
        </section>

        <!-- Feature Cards -->
        <section style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1.5rem; margin-top:2rem;">
            <div class="card" style="text-align:center; padding:2rem;">
                <div style="font-size:2rem; margin-bottom:1rem;">📋</div>
                <h3 style="margin-bottom:0.5rem;">Easy Submission</h3>
                <p style="color:var(--clr-text-muted); font-size:0.875rem;">Submit complaints with photos and detailed descriptions in minutes.</p>
            </div>
            <div class="card" style="text-align:center; padding:2rem;">
                <div style="font-size:2rem; margin-bottom:1rem;">📡</div>
                <h3 style="margin-bottom:0.5rem;">Live Tracking</h3>
                <p style="color:var(--clr-text-muted); font-size:0.875rem;">Follow your complaint from submission to resolution on a live timeline.</p>
            </div>
            <div class="card" style="text-align:center; padding:2rem;">
                <div style="font-size:2rem; margin-bottom:1rem;">⚡</div>
                <h3 style="margin-bottom:0.5rem;">Fast Resolution</h3>
                <p style="color:var(--clr-text-muted); font-size:0.875rem;">Our admin team responds quickly with notes and status updates.</p>
            </div>
        </section>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
