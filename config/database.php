<?php
/**
 * Database connection settings.
 * Edit the four constants below to match your MySQL setup, then leave the
 * rest of this file alone.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'rotary_school');
define('DB_USER', 'root');
define('DB_PASS', 'Tanvi9903@');

/**
 * Returns a shared PDO connection (created once per request).
 * Uses prepared statements everywhere they're needed — this function only
 * sets up the connection itself.
 */
function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak connection details to the browser.
            http_response_code(500);
            die('Database connection failed. Please check config/database.php and try again.');
        }
    }

    return $pdo;
}
