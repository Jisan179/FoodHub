<?php
/**
 * FoodHub - Root Entrypoint Redirect
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: login.php");
}
exit();
