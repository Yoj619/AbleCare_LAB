<?php
// ============================================================
//  AbleCare – Database Connection (backend API)
//  Mirrors web-app/db.php so both layers share credentials.
// ============================================================

declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ablecare_dp');
define('DB_CHARSET', 'utf8mb4');

function get_db(): mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);
    return $conn;
}
