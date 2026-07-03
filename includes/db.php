<?php
/**
 * db.php — Database Connection (PDO)
 * =====================================
 * LEARNING NOTE: What is PDO?
 * PDO stands for "PHP Data Objects". It is PHP's recommended way to
 * connect to databases. It provides a safe, unified interface that works
 * with MySQL, PostgreSQL, SQLite, and more — just by changing the DSN.
 *
 * Why PDO instead of mysql_* or mysqli_*?
 *   ✅ Supports Prepared Statements → prevents SQL Injection attacks.
 *   ✅ Works with multiple database types (portable code).
 *   ✅ Object-oriented, cleaner code.
 *   ✅ Better error handling using exceptions.
 */

// Load the database credentials we defined in config.php.
// __DIR__ is a magic constant that returns the directory of THIS file.
// So this path resolves to: /your-project/includes/config.php
require_once __DIR__ . '/config.php';

/**
 * get_db_connection()
 * --------------------
 * Returns a single shared PDO connection instance.
 *
 * LEARNING NOTE: Why use a function instead of just connecting at the top?
 * By wrapping the connection in a function and using a static variable,
 * we ensure only ONE connection is ever made per request (Singleton pattern).
 * This avoids opening multiple database connections, which wastes resources.
 *
 * @return PDO  The active database connection object.
 */
function get_db_connection(): PDO {
    // 'static' means this variable persists between calls to this function.
    // The first call creates the connection; subsequent calls return it.
    static $pdo = null;

    if ($pdo === null) {
        /**
         * LEARNING NOTE: What is a DSN?
         * DSN = Data Source Name. It is a string that tells PDO:
         *   - WHICH database driver to use (mysql:)
         *   - WHERE the server is (host=localhost)
         *   - WHICH database to select (dbname=foodcare_db)
         *   - WHAT character set to use (charset=utf8mb4)
         *
         * utf8mb4 supports ALL unicode characters, including emojis. 🎉
         */
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        // PDO Options:
        $options = [
            // ERRMODE_EXCEPTION: If a query fails, PHP throws an Exception
            // instead of silently returning false. This makes bugs much easier
            // to find during development.
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            // FETCH_ASSOC: By default, when fetching rows, return an
            // associative array like ['name' => 'John'] instead of a
            // numeric array like [0 => 'John']. Much easier to read!
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // EMULATE_PREPARES: false means we use REAL prepared statements
            // from the MySQL server itself, not PHP emulations. This is the
            // most secure option.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Create the PDO connection.
            // If this fails (e.g., MySQL not running), it throws a PDOException.
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // LEARNING NOTE: Never show raw database errors to users in production!
            // In production, log the error to a file instead:
            //   error_log($e->getMessage());
            // For now, we show it to help you debug during development.
            die('<div style="font-family:monospace;background:#1a1a2e;color:#e94560;padding:2rem;border-radius:8px;margin:2rem auto;max-width:600px;">
                    <h2>⚠️ Database Connection Failed</h2>
                    <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                    <hr style="border-color:#333">
                    <p><strong>Checklist:</strong></p>
                    <ol>
                        <li>Is WampServer running? (Icon should be <span style="color:#4caf50">green</span>)</li>
                        <li>Is MySQL service started in WampServer?</li>
                        <li>Have you run <a href="/setup.php" style="color:#f39c12">setup.php</a> to create the database?</li>
                        <li>Are the credentials in <code>includes/config.php</code> correct?</li>
                    </ol>
                 </div>');
        }
    }

    return $pdo;
}
