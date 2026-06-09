<?php
// AbleCare – Database Configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password
define('DB_NAME', 'ablecare_db');

function getDBConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // In production, log this error instead of displaying it
        error_log('Database connection failed: ' . $conn->connect_error);
        die(json_encode(['error' => 'Database connection failed. Please try again later.']));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
