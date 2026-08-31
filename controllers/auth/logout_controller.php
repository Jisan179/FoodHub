<?php
/**
 * FoodHub - Procedural Logout Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

$login_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../login.php' : 'login.php';
header("Location: " . $login_path);
exit();
