<?php
/**
 * register.php — Customer Registration Page
 * Creates a new customer account.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect('/customer_dashboard.php');
}

$error = null; // Stores validation errors
$success = null; // Stores success message if registration works

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Step 1: Read and sanitize form data ──────────────────────────────────
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? ''); // Not stored in DB but good for validation/contact simulation
    $password         = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // ── Step 2: Validate the inputs ──────────────────────────────────────────
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields marked with an asterisk (*) are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // LEARNING NOTE: filter_var()
        //   PHP has built-in helpers to validate emails, numbers, URLs, etc.
        //   FILTER_VALIDATE_EMAIL check ensures the email is formatted correctly.
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match. Please verify.';
    } else {
        $pdo = get_db_connection();

        // ── Step 3: Check if email is already taken ──────────────────────────
        // Since we defined the email column as UNIQUE in our database,
        // trying to insert a duplicate email would trigger a database crash.
        // We prevent this by checking first.
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $error = 'An account with this email address already exists.';
        } else {
            // ── Step 4: Securely hash the password ────────────────────────────
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                // ── Step 5: Insert user into database ─────────────────────────
                // LEARNING NOTE: INSERT query
                //   INSERT INTO table (columns...) VALUES (placeholders...)
                //   Notice we do not set 'role' or 'id'.
                //   'id' is AUTO_INCREMENT, and 'role' defaults to 'customer' in DB.
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password_hash, role)
                    VALUES (:name, :email, :password_hash, 'customer')
                ");
                $stmt->execute([
                    ':name'          => $name,
                    ':email'         => $email,
                    ':password_hash' => $password_hash,
                ]);

                // Success! Set a flash message and redirect to login page.
                set_flash('success', 'Account created successfully! You can now log in. 🎉');
                redirect('/login.php');

            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page-wrapper">
    <div class="container flex-center" style="min-height:80vh;">
        <div class="card--glass" style="width:100%; max-width:480px; padding:2.5rem;">

            <div style="text-align:center; margin-bottom:2rem;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">👤</div>
                <h1 style="font-size:1.75rem; font-weight:800; margin-bottom:0.25rem;">Create Account</h1>
                <p style="color:var(--clr-text-muted); font-size:0.875rem;">Join FoodCare and start tracking complaints</p>
            </div>

            <!-- Registration form -->
            <form method="POST" action="<?= BASE_PATH ?>/register.php" id="register-form" novalidate>
                
                <?php if ($error): ?>
                <div class="alert alert--error" style="margin-bottom:1.25rem;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-input"
                           placeholder="John Doe" required autocomplete="name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="you@example.com" required autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input"
                           placeholder="+1 (555) 000-0000" required autocomplete="tel"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Min. 8 characters" required autocomplete="new-password" minlength="8">
                    <span class="form-hint">Use at least 8 characters.</span>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                           placeholder="Repeat your password" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn--primary btn--full btn--lg" id="register-submit-btn">
                    Create My Account
                </button>
            </form>

            <p style="text-align:center; margin-top:1.5rem; font-size:0.875rem; color:var(--clr-text-muted);">
                Already have an account?
                <a href="/login.php" id="register-login-link">Sign in here</a>
            </p>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
