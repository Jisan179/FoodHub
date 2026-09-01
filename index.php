<?php
/**
 * FoodHub - Root Entrypoint Redirect
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'Customer') {
        header("Location: customer/dashboard.php");
    } else {
        header("Location: login.php");
    }
} else {
    header("Location: login.php");
}
exit();
