<?php
require_once __DIR__ . '/session.php';

// User auth helpers
function user_is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}
function user_id() {
    return $_SESSION['user_id'] ?? null;
}
function require_user_login() {
    if (!user_is_logged_in()) {
        go('login.php');
    }
}

// Admin auth helpers
function admin_is_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}
function admin_id() {
    return $_SESSION['admin_id'] ?? null;
}
function require_admin_login() {
    if (!admin_is_logged_in()) {
        go('login.php'); // admin/login.php will include relative path properly
    }
}
?>