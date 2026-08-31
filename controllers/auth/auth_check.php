<?php
/**
 * FoodHub - Procedural Auth Guard
 * Session check for Admin authorization
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    // If inside admin/ subfolder, go up one level to login.php, else to login.php
    $login_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../login.php' : 'login.php';
    header("Location: " . $login_path);
    exit();
}
