<?php
// ============================================================
//  AbleCare – Database Connection (db.php)
//  Place this file in the same folder as your other PHP files.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // ← change to your MySQL username
define('DB_PASS', '');            // ← change to your MySQL password
define('DB_NAME', 'ablecare_dp');
define('DB_CHARSET', 'utf8mb4');

function get_db(): mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        // In production, log this and show a generic error page
        die('<p style="font-family:sans-serif;color:#c0392b;padding:24px;">'
          . '⚠ Database connection failed: '
          . htmlspecialchars($conn->connect_error)
          . '<br>Please check your <code>db.php</code> credentials.</p>');
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}
