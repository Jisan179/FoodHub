<?php
/**
 * FoodHub - Sends each signed-in user to the dashboard of their role
 */
require_once __DIR__ . '/includes/auth_check.php';

check_auth();
header("Location: " . get_user_dashboard_url());
exit();
