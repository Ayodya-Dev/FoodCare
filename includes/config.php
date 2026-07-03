<?php
/**
 * config.php — Database Configuration
 * =====================================
 * This is the ONLY file you need to change when moving your project
 * from your local WampServer to a live hosting provider.
 *
 * LOCAL (WampServer default):
 *   DB_HOST = 'localhost'
 *   DB_USER = 'root'
 *   DB_PASS = ''         <-- WampServer default has no password
 *   DB_NAME = 'foodcare_db'
 *
 * LIVE HOSTING:
 *   Your hosting provider (e.g., cPanel) will give you a database
 *   username, password, and database name when you create a MySQL DB.
 *   Just replace the values below with those credentials.
 */

// ── Database Host ────────────────────────────────────────────────────────────
// Usually 'localhost' for both local and most shared hosting providers.
define('DB_HOST', 'localhost');

// ── Database Port ────────────────────────────────────────────────────────────
// WampServer MySQL defaults to port 3308. Production hosts usually use 3306.
define('DB_PORT', '3308');

// ── Database Username ────────────────────────────────────────────────────────
// WampServer local default is 'root'. Change for live hosting.
define('DB_USER', 'root');

// ── Database Password ────────────────────────────────────────────────────────
// WampServer local default is an empty string ''. Change for live hosting.
define('DB_PASS', '');

// ── Database Name ────────────────────────────────────────────────────────────
// The name of the MySQL database we will create using setup.php.
define('DB_NAME', 'foodcare_db');

// ── Application Base URL ─────────────────────────────────────────────────────
// Used to build absolute links (e.g., for redirects and asset paths).
// LOCAL: 'http://localhost/FoodCare'
// LIVE:  'https://yourdomain.com'
define('BASE_URL', 'http://localhost/FoodCare');

// ── Application Base Path ────────────────────────────────────────────────────
// LEARNING NOTE: What is BASE_PATH?
//   When your app lives in a SUBFOLDER (e.g. localhost/FoodCare/),
//   all absolute links like '/css/style.css' break because the browser
//   looks for localhost/css/style.css instead of localhost/FoodCare/css/style.css.
//
//   BASE_PATH stores the subfolder prefix ('/FoodCare') so we can write:
//     BASE_PATH . '/css/style.css'   → /FoodCare/css/style.css  ✅
//
//   If you ever move the app to the ROOT of a domain, just set this to ''.
define('BASE_PATH', '/FoodCare');

// ── Application Name ─────────────────────────────────────────────────────────
define('APP_NAME', 'FoodCare');
define('APP_TAGLINE', 'BiteCraft Kitchen — Complaint Portal');

// ── Upload Directory ─────────────────────────────────────────────────────────
// Path where customer complaint photos and product images are stored.
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PRODUCTS_DIR', __DIR__ . '/../assets/products/');
