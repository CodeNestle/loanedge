<?php
// DB connection settings. Namma DB name "loanedge". Adjust host/user/pass as needed.
$DB_HOST = 'sql311.ezyro.com';
$DB_USER = 'ezyro_39312727';        // change if needed
$DB_PASS = '803cdbac3c33';            // change if needed
$DB_NAME = 'ezyro_39312727_loanedge';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('Database connect failed: ' . $mysqli->connect_error);
}

// Optional: set charset utf8mb4
$mysqli->set_charset('utf8mb4');
?>

