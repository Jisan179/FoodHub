<?php
/**
 * FoodHub - Procedural Auth Guard for Customer Module
 * Session check for Customer authorization
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Customer') {
    // If inside customer/ or actions/ subfolder, go up to login.php, else login.php
    $in_actions = (strpos($_SERVER['PHP_SELF'], '/customer/actions/') !== false);
    $in_customer = (strpos($_SERVER['PHP_SELF'], '/customer/') !== false);
    
    if ($in_actions) {
        $login_path = '../../login.php';
    } elseif ($in_customer) {
        $login_path = '../login.php';
    } else {
        $login_path = 'login.php';
    }
    
    header("Location: " . $login_path);
    exit();
}
