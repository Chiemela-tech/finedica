<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'finedica');
define('DB_NAME', 'user_reg_db');
define('DB_PORT', 3307); // Change this to 3307 if your MySQL runs on 3307

// Function to establish a database connection
function getDatabaseConnection() {
    static $conn = null;

    if ($conn === null) {
        file_put_contents(__DIR__ . '/debug_log.txt', "getDatabaseConnection called\n", FILE_APPEND);
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

            if ($conn->connect_error) {
                file_put_contents(__DIR__ . '/debug_log.txt', "DB connect_error: " . $conn->connect_error . "\n", FILE_APPEND);
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }

            // Set character encoding for proper data handling
            $conn->set_charset("utf8mb4");
            file_put_contents(__DIR__ . '/debug_log.txt', "DB connection established\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/debug_log.txt', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log($e->getMessage(), 3, __DIR__ . '/error_log.txt'); // Log errors to a file
            die("We are experiencing technical difficulties. Please try again later."); // User-friendly message
        }
    }

    return $conn;
}

// Function to close the database connection
function closeDatabaseConnection() {
    $conn = getDatabaseConnection();
    if ($conn) {
        $conn->close();
    }
}

// Register shutdown function to ensure the connection is closed
register_shutdown_function('closeDatabaseConnection');
?>