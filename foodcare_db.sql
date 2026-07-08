-- ============================================================
-- FoodCare Database — Manual Import File
-- ============================================================
-- HOW TO USE:
--   1. Open phpMyAdmin → http://localhost/phpmyadmin
--   2. Create a database named: foodcare_db
--   3. Click on foodcare_db in the left sidebar
--   4. Click the "Import" tab at the top
--   5. Choose this file → Click "Go"
-- ============================================================

-- Tell MySQL to use our database
USE foodcare_db;

-- ============================================================
-- TABLE 1: users
-- Stores all registered customers and admin accounts.
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(191) NOT NULL UNIQUE,  -- 191 × 4 bytes = 764 (under 1000 limit)
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 2: products
-- Stores the food items customers can complain about.
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    price       DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    image       VARCHAR(255),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 3: complaints
-- Stores every complaint linked to a user and a product.
-- FOREIGN KEY means: user_id must match a real row in users(id)
-- ============================================================
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
);

-- ============================================================
-- SEED: Default Admin Account
-- Password is: password
-- (hashed with PHP password_hash() bcrypt)
-- ============================================================
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES (
    'Admin',
    'admin@foodcare.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

-- ============================================================
-- SEED: Sample Food Products
-- ============================================================
INSERT IGNORE INTO products (id, name, description, price) VALUES
(1, 'Spicy Chicken Burger',  'Crispy fried chicken with jalapeños and sriracha mayo.',  8.99),
(2, 'Classic Beef Burger',   'Juicy beef patty with lettuce, tomato and cheddar.',      9.49),
(3, 'Margherita Pizza',      'Fresh tomato base, mozzarella and basil.',               12.99),
(4, 'BBQ Pulled Pork Wrap',  'Slow-cooked pulled pork with tangy BBQ sauce.',           7.99),
(5, 'Truffle Mac & Cheese',  'Creamy mac & cheese with a hint of black truffle.',      10.49),
(6, 'Garden Veggie Bowl',    'Roasted seasonal veggies over herbed quinoa.',            8.49);
