<?php
/**

 * FoodHub - Root Entrypoint Router
 */

require_once __DIR__ . '/includes/auth_check.php';

if (is_logged_in()) 
    header("Location: " . get_user_dashboard_url());

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
