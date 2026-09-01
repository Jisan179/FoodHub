<?php
// manager/includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    // Determine path back to login based on current directory depth
    $path = strpos($_SERVER['PHP_SELF'], '/manager/views/') !== false || strpos($_SERVER['PHP_SELF'], '/manager/controllers/') !== false 
        ? '../../login.php' 
        : '../login.php';
        
    header("Location: $path");
    exit();
}
