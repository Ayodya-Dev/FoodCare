<?php
/**
 * setup.php — One-Time Database Setup Script
 * ============================================
 * Run this script ONCE to create your database tables and seed initial data.
 * Visit: http://localhost/setup.php
 *
 * LEARNING NOTE: What is "seeding"?
 * Seeding means inserting some default/demo data so the app has something
 * to show. For us, that means creating an admin user and some food products.
 *
 * 🔒 SECURITY WARNING: Delete or rename this file after running it in production.
 * Anyone who visits this URL could re-run it!
 */

// Load our DB credentials and the get_db_connection() function.
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// ── Helper: Print a styled result message ─────────────────────────────────────
function result(string $icon, string $label, string $msg): void {
    echo "<tr>
        <td style='padding:0.5rem 1rem; font-size:1.25rem;'>$icon</td>
        <td style='padding:0.5rem 1rem; font-weight:600;'>$label</td>
        <td style='padding:0.5rem 1rem; color:#94a3b8;'>$msg</td>
    </tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FoodCare — Database Setup</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 2rem; max-width: 700px; margin: 0 auto; }
        h1 { font-size: 1.75rem; margin-bottom: 0.5rem; }
        p  { color: #94a3b8; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        .done-btn { display:inline-block; margin-top:1.5rem; background:#f97316; color:#fff;
                    padding:0.75rem 1.5rem; border-radius:8px; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
<div class="card">
    <h1>⚙️ FoodCare — Database Setup</h1>
    <p>Creating tables and seeding initial data…</p>
    <table>
<?php

// ── Get our single shared DB connection ───────────────────────────────────────
// LEARNING NOTE: We use get_db_connection() from db.php.
// It returns a PDO object we'll use to run SQL queries.
$pdo = get_db_connection();

// =============================================================================
// TABLE 1: users
// =============================================================================
// LEARNING NOTE: What is a PRIMARY KEY?
//   Every row in a table needs a unique identifier — that's the primary key.
//   AUTO_INCREMENT means MySQL automatically assigns the next number (1, 2, 3…)
//   so we never have to think about it.
//
// LEARNING NOTE: What is VARCHAR(255)?
//   VARCHAR means "variable-length string". The (255) is the maximum characters
//   allowed. It uses only the space needed — 'John' takes 4 bytes, not 255.
//
// LEARNING NOTE: NOT NULL vs NULL
//   NOT NULL means the column MUST have a value. You can't leave it blank.
//   NULL means the column is optional.
//
// LEARNING NOTE: ENUM
//   ENUM restricts a column to only accept specific values.
//   role ENUM('customer','admin') means only those two strings are allowed.
//   MySQL will reject any other value — a built-in safety guard!
//
// LEARNING NOTE: DEFAULT
//   Sets the automatic value if you don't specify one during INSERT.
//   DEFAULT 'customer' means new users are customers unless stated otherwise.
//
// LEARNING NOTE: TIMESTAMP and CURRENT_TIMESTAMP
//   TIMESTAMP stores date+time. CURRENT_TIMESTAMP means "use right now"
//   as the default — so created_at auto-fills when a row is inserted.
// =============================================================================
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            name          VARCHAR(100) NOT NULL,
            email         VARCHAR(191) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    result('✅', 'users', 'Table created (or already exists).');
} catch (PDOException $e) {
    result('❌', 'users', $e->getMessage());
}


// =============================================================================
// TABLE 2: products
// =============================================================================
// LEARNING NOTE: TEXT vs VARCHAR
//   TEXT can hold up to 65,535 characters. Use it for long content like
//   descriptions. VARCHAR is better for short, indexed fields like names.
//
// LEARNING NOTE: DECIMAL(10, 2)
//   Stores exact decimal numbers. (10, 2) means: up to 10 total digits,
//   with 2 after the decimal point. Perfect for prices like 299.99.
//   Never use FLOAT for money — floating-point math has rounding errors!
// =============================================================================
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            name        VARCHAR(150) NOT NULL,
            description TEXT,
            price       DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            image       VARCHAR(255),
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    result('✅', 'products', 'Table created (or already exists).');
} catch (PDOException $e) {
    result('❌', 'products', $e->getMessage());
}


// =============================================================================
// TABLE 3: complaints
// =============================================================================
// LEARNING NOTE: FOREIGN KEY
//   A foreign key links a column in one table to the primary key of another.
//   user_id INT references users(id) means every complaint MUST belong to
//   a real user. MySQL will refuse to insert a complaint with a made-up user_id.
//
//   ON DELETE CASCADE: If a user is deleted, all their complaints are deleted too.
//   ON DELETE SET NULL: If the linked product is deleted, product_id becomes NULL
//   (we keep the complaint, we just lose the product link).
//
// LEARNING NOTE: Multi-value ENUM
//   status ENUM('new','in_progress','resolved','closed')
//   This is the "state machine" for a complaint — it can only move
//   through these defined states.
// =============================================================================
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS complaints (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            user_id     INT NOT NULL,
            product_id  INT,
            category    ENUM('Food Quality','Packaging','Delivery','Hygiene','Other') NOT NULL,
            priority    ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
            description TEXT NOT NULL,
            photo       VARCHAR(255),
            status      ENUM('new','in_progress','resolved','closed') NOT NULL DEFAULT 'new',
            admin_note  TEXT,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        )
    ");
    result('✅', 'complaints', 'Table created (or already exists).');
} catch (PDOException $e) {
    result('❌', 'complaints', $e->getMessage());
}


// =============================================================================
// SEED DATA: Default Admin User
// =============================================================================
// LEARNING NOTE: password_hash()
//   We NEVER store plain passwords. password_hash() transforms 'admin123'
//   into a long scrambled string like '$2y$10$abc...xyz'.
//   Even if your database is stolen, the attacker can't reverse it.
//   PASSWORD_DEFAULT uses PHP's best available algorithm (currently bcrypt).
//
// LEARNING NOTE: Prepared Statements
//   Instead of building SQL strings manually (which risks SQL Injection!),
//   we use placeholders (:email, :name, etc.) and then bind real values.
//   PDO escapes the values safely before sending them to MySQL.
//   This is the #1 way to prevent SQL Injection attacks.
//
//   SQL Injection example (BAD):
//     "INSERT INTO users WHERE email = '" . $_POST['email'] . "'"
//   If someone types:  ' OR 1=1; DROP TABLE users; --
//   ...your whole database is gone! Prepared statements prevent this.
// =============================================================================
try {
    // Check if admin already exists — we don't want duplicates
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => 'admin@foodcare.com']);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role)
            VALUES (:name, :email, :hash, 'admin')
        ");
        $stmt->execute([
            ':name'  => 'Admin',
            ':email' => 'admin@foodcare.com',
            ':hash'  => password_hash('admin123', PASSWORD_DEFAULT),
        ]);
        result('✅', 'Admin User', 'Created — Email: admin@foodcare.com / Pass: admin123');
    } else {
        result('ℹ️', 'Admin User', 'Already exists — skipped.');
    }
} catch (PDOException $e) {
    result('❌', 'Admin User', $e->getMessage());
}


// =============================================================================
// SEED DATA: Sample Food Products
// =============================================================================
// LEARNING NOTE: rowCount() vs fetch()
//   After a SELECT, rowCount() isn't reliable in all PDO drivers.
//   It's safer to use fetch() and check if it returns false (no row found).
// =============================================================================
$sample_products = [
    ['Spicy Chicken Burger',    'Crispy fried chicken with jalapeños and sriracha mayo.',   8.99],
    ['Classic Beef Burger',     'Juicy beef patty with lettuce, tomato and cheddar.',       9.49],
    ['Margherita Pizza',        'Fresh tomato base, mozzarella and basil.',                12.99],
    ['BBQ Pulled Pork Wrap',    'Slow-cooked pulled pork with tangy BBQ sauce.',            7.99],
    ['Truffle Mac & Cheese',    'Creamy mac & cheese with a hint of black truffle.',       10.49],
    ['Garden Veggie Bowl',      'Roasted seasonal veggies over herbed quinoa.',             8.49],
];

try {
    // Only seed if the products table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM products");
    $count = $stmt->fetch()['cnt'];

    if ($count == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price)
            VALUES (:name, :description, :price)
        ");
        foreach ($sample_products as [$name, $desc, $price]) {
            // LEARNING NOTE: Named placeholders (:name) are cleaner than ? marks
            // for queries with multiple parameters. Order doesn't matter.
            $stmt->execute([':name' => $name, ':description' => $desc, ':price' => $price]);
        }
        result('✅', 'Products', count($sample_products) . ' sample products inserted.');
    } else {
        result('ℹ️', 'Products', "$count products already exist — skipped.");
    }
} catch (PDOException $e) {
    result('❌', 'Products', $e->getMessage());
}

?>
    </table>

    <a href="/index.php" class="done-btn">🚀 Setup Complete — Go to App</a>
    <p style="margin-top:1rem; font-size:0.8rem; color:#64748b;">
        💡 Tip: In a real production app, delete or restrict access to this file after running it.
    </p>
</div>
</body>
</html>
