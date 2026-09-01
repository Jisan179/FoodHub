<?php
/**
 * FoodHub - Root Entrypoint Router
 */

require_once __DIR__ . '/includes/auth_check.php';

if (is_logged_in()) {
    header("Location: " . get_user_dashboard_url());
    exit();
}

header("Location: login.php");
exit();
