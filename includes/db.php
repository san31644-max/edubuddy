<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
function db(): mysqli {
    static $db;
    if ($db instanceof mysqli) return $db;
    mysqli_report(MYSQLI_REPORT_OFF);
    $db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) { http_response_code(503); exit('K Education cannot connect right now. Please start MySQL and check includes/config.php.'); }
    $db->set_charset('utf8mb4');
    return $db;
}
