<?php
/**
 * register.php — Customer Registration Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect('/customer_dashboard.php');
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $agreed = isset($_POST['terms']);

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Full Name, Email, Password and Confirm Password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match. Please try again.';
    } elseif (!$agreed) {
        $error = 'You must agree to the Terms of Service and Privacy Policy.';
    } else {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, organization, password_hash, role)
                    VALUES (:name, :email, :phone, :organization, :password_hash, 'customer')
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone ?: null,
                    ':organization' => $organization ?: null,
                    ':password_hash' => $password_hash,
                ]);
                set_flash('success', 'Account created! Welcome to FoodCare 🎉');
                redirect('/login.php');
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Create your FoodCare account';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* ══ Register Page — matches screenshot exactly ══════════════════ */
    .reg-wrap {
        min-height: 100vh;
        background: var(--fc-bg);
        padding-top: var(--navbar-h);
        display: flex;
        flex-direction: column;
    }

    .reg-back {
        padding: 1.5rem 0 0;
    }

    .reg-back a {
        font-size: 0.875rem;
        color: var(--fc-muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: color 0.15s;
    }

    .reg-back a:hover {
        color: var(--fc-black);
    }

    .reg-center {
        flex: 0 0 auto;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 2rem 1rem 0;
    }

    .reg-card {
        background: var(--fc-white);
        border: 1px solid var(--fc-border);
        border-radius: var(--radius-xl);
        padding: 2.25rem 2.5rem 2rem;
        width: 100%;
        max-width: 500px;
    }

    .reg-title {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--fc-black);
        margin-bottom: 0.3rem;
        line-height: 1.25;
    }

    .reg-sub {
        font-size: 0.875rem;
        color: var(--fc-muted);
        margin-bottom: 1.75rem;
    }

    /* Error / info */
    .reg-error {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #DC2626;
        margin-bottom: 1.25rem;
    }

    .reg-infobox {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        background: #FFF7F0;
        border: 1px solid #FFD6B0;
        border-radius: 8px;
        padding: 0.875rem 1rem;
        margin-top: 1.1rem;
        font-size: 0.8125rem;
        color: var(--fc-muted);
        line-height: 1.55;
    }

    .reg-infobox svg {
        width: 16px;
        height: 16px;
        stroke: var(--fc-primary);
        fill: none;
        stroke-width: 1.8;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* Form layout */
    .reg-group {
        margin-bottom: 1rem;
    }

    .reg-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .reg-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--fc-black);
        display: block;
        margin-bottom: 0.35rem;
    }

    .reg-label span {
        font-size: 0.75rem;
        font-weight: 400;
        color: var(--fc-muted);
        margin-left: 0.25rem;
    }

    .reg-field {
        position: relative;
    }

    .reg-field-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        display: flex;
        align-items: center;
    }

    .reg-field-icon svg {
        width: 15px;
        height: 15px;
        stroke: #9CA3AF;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .reg-input {
        width: 100%;
        padding: 0.65rem 1rem 0.65rem 2.5rem;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        background: #fff;
        font-size: 0.9rem;
        color: var(--fc-black);
        font-family: var(--font-base);
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .reg-input::placeholder {
        color: #9CA3AF;
    }

    .reg-input:focus {
        border-color: var(--fc-primary);
        box-shadow: 0 0 0 3px rgba(255, 122, 26, 0.12);
    }

    /* Checkbox */
    .reg-terms {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        font-size: 0.8125rem;
        color: var(--fc-muted);
        margin-bottom: 1.25rem;
        cursor: pointer;
        line-height: 1.5;
    }

    .reg-terms input[type="checkbox"] {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        margin-top: 2px;
        accent-color: var(--fc-primary);
    }

    .reg-terms a {
        color: var(--fc-primary);
        text-decoration: none;
        font-weight: 500;
    }

    .reg-terms a:hover {
        color: var(--fc-primary-dark);
    }

    /* Submit */
    .reg-btn {
        width: 100%;
        padding: 0.825rem;
        background: var(--fc-primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        font-family: var(--font-base);
        transition: background 0.15s;
    }

    .reg-btn:hover {
        background: var(--fc-primary-dark);
    }

    /* Already have account */
    .reg-foot {
        text-align: center;
        padding: 1.25rem 0;
        font-size: 0.875rem;
        color: var(--fc-muted);
    }

    .reg-foot a {
        color: var(--fc-primary);
        font-weight: 600;
        text-decoration: none;
    }

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

    .lp-footer-inner a {
        color: var(--fc-muted);
        text-decoration: none;
        margin-left: 1rem;
    }

    .lp-footer-inner a:hover {
        color: var(--fc-black);
    }

    @media (max-width: 560px) {
        .reg-row {
            grid-template-columns: 1fr;
        }

        .reg-card {
            padding: 1.75rem 1.25rem;
        }
    }
</style>

<div class="reg-wrap">

    <!-- Back to Home -->
    <div class="container reg-back">
        <a href="<?= BASE_PATH ?>/index.php" id="reg-back-home">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Back to Home
        </a>
    </div>

    <div class="reg-center">
        <div class="reg-card">

            <h1 class="reg-title">Create your FoodCare account</h1>
            <p class="reg-sub">Create an account to submit and track complaints.</p>

            <?php if ($error): ?>
                <div class="reg-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_PATH ?>/register.php" id="register-form" novalidate>

                <!-- Full Name -->
                <div class="reg-group">
                    <label for="name" class="reg-label">Full Name</label>
                    <div class="reg-field">
                        <span class="reg-field-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" id="name" name="name" class="reg-input" placeholder="Enter your full name"
                            required autocomplete="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                </div>

                <!-- Email Address -->
                <div class="reg-group">
                    <label for="email" class="reg-label">Email Address</label>
                    <div class="reg-field">
                        <span class="reg-field-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" class="reg-input" placeholder="name@example.com"
                            required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <!-- Password + Confirm Password (2 cols) -->
                <div class="reg-row">
                    <div>
                        <label for="password" class="reg-label">Password</label>
                        <div class="reg-field">
                            <span class="reg-field-icon">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="reg-input"
                                placeholder="••••••••" required autocomplete="new-password" minlength="8">
                        </div>
                    </div>
                    <div>
                        <label for="confirm_password" class="reg-label">Confirm Password</label>
                        <div class="reg-field">
                            <span class="reg-field-icon">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <input type="password" id="confirm_password" name="confirm_password" class="reg-input"
                                placeholder="••••••••" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <!-- Phone + Organization (2 cols) -->
                <div class="reg-row">
                    <div>
                        <label for="phone" class="reg-label">Phone Number <span>(Optional)</span></label>
                        <div class="reg-field">
                            <span class="reg-field-icon">
                                <svg viewBox="0 0 24 24">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.38 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.59a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </span>
                            <input type="tel" id="phone" name="phone" class="reg-input" placeholder="+1 (555) 000-0000"
                                autocomplete="tel" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div>
                        <label for="organization" class="reg-label">Organization <span>(Optional)</span></label>
                        <div class="reg-field">
                            <span class="reg-field-icon">
                                <svg viewBox="0 0 24 24">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </span>
                            <input type="text" id="organization" name="organization" class="reg-input"
                                placeholder="Company or Group name"
                                value="<?= htmlspecialchars($_POST['organization'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Terms checkbox -->
                <label class="reg-terms">
                    <input type="checkbox" name="terms" id="terms-checkbox" <?= isset($_POST['terms']) ? 'checked' : '' ?>>
                    <span>By creating an account, you agree to our <a href="#" id="reg-terms-link">Terms of Service</a>
                        and &nbsp;<a href="#" id="reg-privacy-link">Privacy Policy.</a></span>
                </label>

                <!-- Submit -->
                <button type="submit" class="reg-btn" id="register-submit-btn">
                    Create account
                </button>

                <!-- Info box -->
                <div class="reg-infobox">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>Upon registration, you will be automatically redirected to your personalized dashboard where
                        you can start managing your complaints immediately.</span>
                </div>

            </form>

        </div>
    </div>

    <!-- Already have account -->
    <div class="reg-foot">
        Already have an account? <a href="<?= BASE_PATH ?>/login.php" id="reg-login-link">Log in</a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>