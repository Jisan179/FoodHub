<?php
/**
 * FoodHub - Root Entrypoint Router
 */

require_once __DIR__ . '/includes/auth_check.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: login.php");
}
exit();
