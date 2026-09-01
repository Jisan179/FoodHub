<?php
/**
 * FoodHub - Procedural Auth Guard for Riders
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Rider') {
    $is_controller_path = strpos($_SERVER['PHP_SELF'], '/controllers/rider/') !== false;
    $is_rider_path = strpos($_SERVER['PHP_SELF'], '/rider/') !== false;
    $login_path = $is_controller_path ? '../../login.php' : ($is_rider_path ? '../login.php' : 'login.php');
    header('Location: ' . $login_path);
    exit();
}
