<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!admin_is_logged_in()) { go('login.php'); }

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    // First, enable cascade in the DB schema (manual step).
    $stmt = $mysqli->prepare('DELETE FROM loans WHERE loan_type_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    $stmt2 = $mysqli->prepare('DELETE FROM loan_types WHERE id=?');
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $stmt2->close();
}
go('loan_types.php');
?>