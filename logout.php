<?php
/**
 * logout.php — Logout Handler
 * Destroys the user session and redirects to the homepage.
 */
require_once __DIR__ . '/includes/auth.php';

// Destroy the session
session_destroy();

// Redirect to homepage with a flash message
// (We can't use set_flash here since session is destroyed, so we use a query string)
redirect('/login.php?logged_out=1');
