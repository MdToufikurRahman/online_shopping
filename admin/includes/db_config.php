<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'cbpos_db';

try {
    $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Log error (server-side)
    error_log('Database connection failed: ' . $e->getMessage());

    // Stop execution with a clean message
    http_response_code(500);
    exit('Database connection error. Please try again later.');
}
