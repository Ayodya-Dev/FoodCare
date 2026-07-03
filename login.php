<?php
/**
 * login.php — User Login Page
 * Authenticates customers and admins.
 * On success: redirects to correct dashboard based on role.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Already logged in? Redirect away
if (is_logged_in()) {
    redirect(is_admin() ? '/admin/dashboard.php' : '/customer_dashboard.php');
}

// =============================================================================
// HANDLE FORM SUBMISSION (POST REQUEST)
// =============================================================================
$error = null; // Will hold an error message string if login fails

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // ── Step 2: Basic validation ───────────────────────────────────────────────
    // LEARNING NOTE: Why validate BEFORE hitting the database?
    //   There's no point querying MySQL for an empty email. We fail fast,
    //   save a DB round-trip, and give the user a clear message.
    if (empty($email) || empty($password)) {
        $error = 'Please fill in both email and password.';

    } else {
        // ── Step 3: Look up the user in the database ───────────────────────────
        // LEARNING NOTE: SELECT query
        //   SELECT columns FROM table WHERE condition
        //   We're asking: "Find me the row in 'users' where email matches."
        //
        // LEARNING NOTE: LIMIT 1
        //   We only expect one result (email is UNIQUE).
        //   Adding LIMIT 1 tells MySQL to stop searching after the first match.
        //   This is a small performance optimisation.
        //
        // LEARNING NOTE: Why NOT use SELECT * ?
        //   SELECT * fetches ALL columns including ones we don't need.
        //   Naming the columns we want is more efficient and clearer.
        $pdo  = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT id, name, email, password_hash, role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(); // Returns an associative array, or FALSE if not found

        // ── Step 4: Verify the password ────────────────────────────────────────
        // LEARNING NOTE: password_verify()
        //   Remember how we stored passwords with password_hash()? We can NEVER
        //   reverse that hash back to the original password.
        //   Instead, password_verify() hashes the typed password the same way
        //   and checks if the results match.
        //
        //   password_verify('admin123', '$2y$10$abc...') → true or false
        //
        //   We also check $user first — if the email doesn't exist, $user is
        //   FALSE, and we skip the verify step entirely.
        if ($user && password_verify($password, $user['password_hash'])) {

            // ── Step 5: Login success → store user info in SESSION ─────────────
            // LEARNING NOTE: $_SESSION
            //   A session is like a "memory" for a browser visit. PHP keeps a
            //   session file on the server and gives the browser a cookie with
            //   the session ID. On each page load, PHP reads the session file
            //   and populates $_SESSION with the saved data.
            //   This is how the app "remembers" who you are across pages.
            //
            // LEARNING NOTE: session_regenerate_id(true)
            //   After login, always regenerate the session ID. This prevents
            //   "Session Fixation" attacks where an attacker tricks you into
            //   using a session ID they already know. (Security best practice!)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            // ── Step 6: Redirect to the right dashboard ────────────────────────
            // Admins → admin dashboard | Customers → customer dashboard
            set_flash('success', 'Welcome back, ' . $user['name'] . '! 👋');
            redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/customer_dashboard.php');

        } else {
            // Wrong email OR wrong password — we give the SAME vague message
            // LEARNING NOTE: Security — Vague error messages
            //   We deliberately don't say "email not found" or "wrong password"
            //   separately. If we did, an attacker could use the error to
            //   figure out which emails are registered in our system!
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<main class="page-wrapper">
    <div class="container flex-center" style="min-height:80vh;">
        <div class="card--glass" style="width:100%; max-width:440px; padding:2.5rem;">

            <div style="text-align:center; margin-bottom:2rem;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">🔐</div>
                <h1 style="font-size:1.75rem; font-weight:800; margin-bottom:0.25rem;">Welcome Back</h1>
                <p style="color:var(--clr-text-muted); font-size:0.875rem;">Sign in to your FoodCare account</p>
            </div>

            <!-- Login form -->
            <form method="POST" action="<?= BASE_PATH ?>/login.php" id="login-form" novalidate>

                <?php if ($error): ?>
                <!-- LEARNING NOTE: Showing error messages
                     $error is set in our PHP above if login fails.
                     We use htmlspecialchars() to prevent XSS attacks — it
                     converts < > & " to safe HTML entities so a malicious
                     string can't inject HTML into the page. -->
                <div class="alert alert--error" style="margin-bottom:1.25rem;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <!-- value re-populates email after a failed attempt so user doesn't retype it -->
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="you@example.com" required autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn--primary btn--full btn--lg" id="login-submit-btn">
                    Sign In
                </button>
            </form>

            <p style="text-align:center; margin-top:1.5rem; font-size:0.875rem; color:var(--clr-text-muted);">
                Don't have an account?
                <a href="/register.php" id="login-register-link">Create one free</a>
            </p>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
