<?php
// session start central place
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>