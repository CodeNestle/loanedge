<?php
// DB connection settings. Namma DB name "loanedge". Adjust host/user/pass as needed.
$DB_HOST = 'localhost';
$DB_USER = 'root';        // change if needed
$DB_PASS = '';            // change if needed
$DB_NAME = 'loanedge';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('Database connect failed: ' . $mysqli->connect_error);
}

// Optional: set charset utf8mb4
$mysqli->set_charset('utf8mb4');
?>




