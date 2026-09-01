<?php
/**
 * FoodHub - Procedural Auth Guard
 * Session check for Admin authorization
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $login_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/rider/') !== false) ? '../login.php' : 'login.php';
    header("Location: " . $login_path);
    exit();
}
