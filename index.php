<?php
/**
 * FoodHub - Root Entrypoint Redirect
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'Rider') {
    header("Location: rider/dashboard.php");
} else {
    header("Location: login.php");
}
exit();
