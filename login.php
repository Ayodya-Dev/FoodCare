<?php
/**
 * login.php — Unified Login Page
 * Shows Admin Login UI when ?role=admin, otherwise shows User Login UI.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect(is_admin() ? '/admin/dashboard.php' : '/customer_dashboard.php');
}

$is_admin_login = isset($_GET['role']) && $_GET['role'] === 'admin';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in both fields.';
    } else {
        $pdo  = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT id, name, email, password_hash, role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            set_flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/customer_dashboard.php');
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}

$page_title = $is_admin_login ? 'Admin Login' : 'User Login';
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ══ Login Page ══════════════════════════════════════════════════ */
.lp-wrap {
    min-height: 100vh;
    background: var(--fc-bg);
    padding-top: var(--navbar-h);
    display: flex;
    flex-direction: column;
}

/* Back to home */
.lp-back {
    padding: 1.5rem 0 0;
}
.lp-back a {
    font-size: 0.875rem;
    color: var(--fc-muted);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: color var(--transition-fast);
}
.lp-back a:hover { color: var(--fc-black); }

/* Card area */
.lp-center {
    flex: 1;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 2.5rem 1rem 2rem;
}

.lp-card {
    background: var(--fc-white);
    border: 1px solid var(--fc-border);
    border-radius: var(--radius-xl);
    padding: 2.5rem 2.75rem 2rem;
    width: 100%;
    max-width: 460px;
}

/* Lock icon box */
.lp-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: rgba(255,122,26,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.lp-icon-box svg {
    width: 24px;
    height: 24px;
    stroke: var(--fc-primary);
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.lp-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--fc-black);
    margin-bottom: 0.3rem;
    line-height: 1.2;
}
.lp-sub {
    font-size: 0.875rem;
    color: var(--fc-muted);
    margin-bottom: 2rem;
}

/* Error */
.lp-error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #DC2626;
    margin-bottom: 1.25rem;
}

/* Form group */
.lp-group { margin-bottom: 1.1rem; }

.lp-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.4rem;
}
.lp-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--fc-black);
}

/* Input */
.lp-field {
    position: relative;
}
.lp-field-icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #9CA3AF;
    display: flex;
    align-items: center;
}
.lp-field-icon svg {
    width: 16px;
    height: 16px;
    stroke: #9CA3AF;
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.lp-input {
    width: 100%;
    padding: 0.7rem 1rem 0.7rem 2.6rem;
    border: 1px solid #D1D5DB;
    border-radius: var(--radius-md);
    background: #fff;
    font-size: 0.9375rem;
    color: var(--fc-black);
    font-family: var(--font-base);
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.lp-input::placeholder { color: #9CA3AF; }
.lp-input:focus {
    border-color: var(--fc-primary);
    box-shadow: 0 0 0 3px rgba(255,122,26,0.12);
}

/* Remember checkbox */
.lp-remember {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.4rem;
    margin-top: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--fc-muted);
    user-select: none;
}
.lp-remember input[type="checkbox"] {
    width: 15px; height: 15px;
    border: 1px solid #D1D5DB;
    border-radius: 3px;
    cursor: pointer;
    accent-color: var(--fc-black);
    flex-shrink: 0;
}

/* Submit buttons */
.lp-btn {
    width: 100%;
    padding: 0.825rem;
    border: none;
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font-base);
    letter-spacing: 0.01em;
    transition: background 0.15s ease;
}
.lp-btn-admin { background: #0B0B0B; color: #fff; }
.lp-btn-admin:hover { background: #1c1c1c; }
.lp-btn-user  { background: var(--fc-primary); color: #fff; }
.lp-btn-user:hover { background: var(--fc-primary-dark); }

/* Info box */
.lp-infobox {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-radius: var(--radius-md);
    padding: 0.875rem 1rem;
    margin-top: 1.1rem;
    font-size: 0.8125rem;
    color: var(--fc-muted);
    line-height: 1.55;
}
.lp-infobox svg {
    width: 16px; height: 16px;
    stroke: #9CA3AF; fill: none;
    stroke-width: 1.8;
    flex-shrink: 0;
    margin-top: 1px;
}

/* Divider */
.lp-divider {
    border: none;
    border-top: 1px solid var(--fc-border);
    margin: 1.6rem 0 1.25rem;
}

/* Authorized notice */
.lp-notice {
    text-align: center;
    font-size: 0.8125rem;
    color: var(--fc-muted);
    font-style: italic;
    line-height: 1.55;
    margin-bottom: 1.4rem;
    padding: 0 0.5rem;
}

/* Bottom links */
.lp-links {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.lp-links a {
    font-size: 0.8125rem;
    color: var(--fc-muted);
    text-decoration: none;
    transition: color 0.15s;
}
.lp-links a:hover { color: var(--fc-black); }
.lp-links-dot {
    width: 3px; height: 3px;
    border-radius: 50%;
    background: #9CA3AF;
    flex-shrink: 0;
}

/* Switch / register link */
.lp-switch {
    text-align: center;
    font-size: 0.875rem;
    color: var(--fc-muted);
    margin-top: 1.25rem;
}
.lp-switch a { color: var(--fc-primary); font-weight: 600; text-decoration: none; }

/* Page footer */
.lp-footer {
    border-top: 1px solid var(--fc-border);
    padding: 1.25rem 0;
    background: var(--fc-white);
}
.lp-footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    color: var(--fc-muted);
    flex-wrap: wrap;
    gap: 0.5rem;
}
.lp-footer-inner a { color: var(--fc-muted); text-decoration: none; margin-left: 1rem; }
.lp-footer-inner a:hover { color: var(--fc-black); }
</style>

<div class="lp-wrap">

    <!-- Back to Home -->
    <div class="container lp-back">
        <a href="<?= BASE_PATH ?>/index.php" id="back-home-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:2px;"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Home
        </a>
    </div>

    <div class="lp-center">
        <div class="lp-card">

        <?php if ($is_admin_login): ?>
        <!-- ══════════ ADMIN LOGIN ══════════ -->

            <!-- Lock icon -->
            <div class="lp-icon-box">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>

            <h1 class="lp-title">Admin Login</h1>
            <p class="lp-sub">Secure access for FoodCare system administrators.</p>

            <?php if ($error): ?>
            <div class="lp-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_PATH ?>/login.php?role=admin" id="admin-login-form" novalidate>

                <!-- Username -->
                <div class="lp-group">
                    <div class="lp-label-row">
                        <label for="email" class="lp-label">Username</label>
                    </div>
                    <div class="lp-field">
                        <span class="lp-field-icon">
                            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <input type="email" id="email" name="email" class="lp-input"
                               placeholder="admin_username" required autocomplete="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <!-- Password — NO Forgot Password link -->
                <div class="lp-group">
                    <div class="lp-label-row">
                        <label for="password" class="lp-label">Password</label>
                    </div>
                    <div class="lp-field">
                        <span class="lp-field-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </span>
                        <input type="password" id="password" name="password" class="lp-input"
                               placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <!-- Remember -->
                <label class="lp-remember">
                    <input type="checkbox" id="admin-remember" name="remember">
                    Remember this device
                </label>

                <!-- Submit -->
                <button type="submit" class="lp-btn lp-btn-admin" id="admin-login-submit">
                    Login as Admin
                </button>

                <!-- Info box -->
                <div class="lp-infobox">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>Use your pre-configured admin credentials to sign in. If you've lost access, contact the system oversight department.</span>
                </div>

            </form>

            <hr class="lp-divider">

            <p class="lp-notice">
                Authorized Personnel Only. All access attempts are logged and monitored for security compliance.
            </p>



        <?php else: ?>
        <!-- ══════════ USER LOGIN ══════════ -->

            <!-- Icon — centered orange circle -->
            <div style="text-align:center; margin-bottom:1.25rem;">
                <div style="
                    width:56px; height:56px;
                    border-radius:50%;
                    background:rgba(255,122,26,0.12);
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                ">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#FF7A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
            </div>

            <h1 class="lp-title" style="text-align:center;">User Login</h1>
            <p class="lp-sub" style="text-align:center; max-width:280px; margin:0 auto 1.75rem;">Enter your credentials to manage your food safety reports</p>

            <?php if ($error): ?>
            <div class="lp-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_PATH ?>/login.php" id="user-login-form" novalidate>

                <!-- Email or Username -->
                <div class="lp-group">
                    <div class="lp-label-row">
                        <label for="email" class="lp-label">Email or Username</label>
                    </div>
                    <div class="lp-field">
                        <span class="lp-field-icon">
                            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </span>
                        <input type="email" id="email" name="email" class="lp-input"
                               placeholder="alex.brown@example.com" required autocomplete="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <!-- Password with eye toggle -->
                <div class="lp-group" style="margin-bottom:0.4rem;">
                    <div class="lp-label-row">
                        <label for="password" class="lp-label">Password</label>
                    </div>
                    <div class="lp-field">
                        <span class="lp-field-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </span>
                        <input type="password" id="password" name="password" class="lp-input"
                               placeholder="••••••••" required autocomplete="current-password"
                               style="padding-right:2.8rem;">
                        <!-- Eye toggle -->
                        <button type="button" id="user-eye-toggle"
                            onclick="(function(){var i=document.getElementById('password');var btn=document.getElementById('user-eye-toggle');if(i.type==='password'){i.type='text';btn.innerHTML='<svg width=\'16\' height=\'16\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#9CA3AF\' stroke-width=\'1.8\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24\'></path><line x1=\'1\' y1=\'1\' x2=\'23\' y2=\'23\'></line></svg>';}else{i.type='password';btn.innerHTML='<svg width=\'16\' height=\'16\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#9CA3AF\' stroke-width=\'1.8\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\'></path><circle cx=\'12\' cy=\'12\' r=\'3\'></circle></svg>';}})();"
                            style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <!-- Forgot password — orange, right aligned -->
                <div style="text-align:right; margin-bottom:1.25rem;">
                    <a href="#" id="user-forgot-pw" style="font-size:0.8125rem; color:var(--fc-primary); text-decoration:none; font-weight:500;">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="lp-btn lp-btn-user" id="user-login-submit" style="margin-bottom:1.25rem;">
                    Login to Dashboard
                </button>

            </form>




            <!-- New to FoodCare -->
            <div style="text-align:center; font-size:0.875rem; color:var(--fc-muted); margin-bottom:1rem;">
                New to FoodCare? <a href="<?= BASE_PATH ?>/register.php" id="go-register" style="color:var(--fc-primary); font-weight:600; text-decoration:none;">Create an account</a>
            </div>

            <!-- Divider -->
            <div style="height:1px; background:var(--fc-border); margin-bottom:1rem;"></div>

            <!-- Back to Home (inside card) -->
            <div style="text-align:center;">
                <a href="<?= BASE_PATH ?>/index.php" id="user-back-home" style="font-size:0.875rem; color:var(--fc-muted); text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back to Home
                </a>
            </div>




        <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
