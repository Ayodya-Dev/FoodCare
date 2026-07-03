<?php
/**
 * auth.php — Session & Authentication Helpers
 * =============================================
 * This file contains helper functions for managing user sessions,
 * checking login status, and enforcing role-based access control.
 *
 * USAGE: Include this file at the TOP of any page that requires
 * a logged-in user. It calls session_start() automatically.
 *
 * Include it like this in your pages:
 *   require_once __DIR__ . '/includes/auth.php';
 */

// Load config so BASE_PATH and APP constants are always available here.
// 'defined' check prevents loading it twice if another file already did.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/config.php';
}

// LEARNING NOTE: session_start() must be called before ANY output (HTML/echo).
// It initialises PHP's $_SESSION superglobal, which persists data across
// page requests for the same browser session (like staying logged in).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Redirect Helper ──────────────────────────────────────────────────────────

/**
 * redirect($url)
 * ---------------
 * Sends the browser to a new URL and stops execution.
 *
 * LEARNING NOTE: header('Location: ...') sends an HTTP redirect response.
 * We MUST call exit() after it, otherwise PHP continues executing the
 * rest of the script even though the browser has already navigated away!
 *
 * @param string $url  The path to redirect to (relative or absolute).
 */
function redirect(string $url): void {
    // LEARNING NOTE: BASE_PATH prefix for subdirectory hosting
    //   If the URL starts with '/', it's an absolute path like '/login.php'.
    //   We prepend BASE_PATH (e.g. '/FoodCare') so it becomes '/FoodCare/login.php'.
    //   If BASE_PATH is '' (running at root), nothing changes. 
    if (str_starts_with($url, '/')) {
        $url = BASE_PATH . $url;
    }
    header('Location: ' . $url);
    exit();
}

// ── Login Check Helpers ──────────────────────────────────────────────────────

/**
 * is_logged_in()
 * ---------------
 * Returns true if the user has an active session (i.e., they are logged in).
 *
 * @return bool
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * is_admin()
 * -----------
 * Returns true only if the logged-in user has the 'admin' role.
 * Used to protect admin-only pages and features.
 *
 * @return bool
 */
function is_admin(): bool {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * require_login()
 * ----------------
 * Guards a page — if the user is NOT logged in, send them to the login page.
 * Call this at the top of any page that requires authentication.
 *
 * Usage:
 *   require_once 'includes/auth.php';
 *   require_login(); // <-- Add this line to protect a page
 */
function require_login(): void {
    if (!is_logged_in()) {
        // Store intended destination so we can send them back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/login.php');
    }
}

/**
 * require_admin()
 * ----------------
 * Guards a page — if the user is NOT an admin, send them away.
 * Call this at the top of any admin-only page.
 *
 * Usage:
 *   require_once '../includes/auth.php'; // (note: ../ from admin/ folder)
 *   require_admin();
 */
function require_admin(): void {
    require_login(); // First ensure they are logged in at all
    if (!is_admin()) {
        // They are logged in but not an admin — redirect to their dashboard
        redirect('/customer_dashboard.php');
    }
}

// ── Session Data Helpers ─────────────────────────────────────────────────────

/**
 * get_current_user_id()
 * ----------------------
 * Returns the logged-in user's database ID, or null if not logged in.
 *
 * @return int|null
 */
function get_current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * get_current_user_name()
 * ------------------------
 * Returns the logged-in user's display name.
 *
 * @return string
 */
function get_current_user_name(): string {
    return $_SESSION['name'] ?? 'Guest';
}

// ── Flash Message Helpers ────────────────────────────────────────────────────
// Flash messages are one-time notifications shown after a redirect.
// e.g., "Complaint submitted successfully!" shown after form submission.

/**
 * set_flash($type, $message)
 * ---------------------------
 * Stores a flash message in the session to be displayed once on next page load.
 *
 * @param string $type     'success', 'error', 'warning', or 'info'
 * @param string $message  The message text to display.
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * get_flash()
 * ------------
 * Retrieves and CLEARS the flash message from the session.
 * Returns null if no flash message exists.
 *
 * @return array|null  ['type' => '...', 'message' => '...'] or null
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // Clear it so it only shows once
        return $flash;
    }
    return null;
}
