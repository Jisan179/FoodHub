<?php
require_once __DIR__ . '/controllers/dashboard_controller.php';

// Route to the corresponding role dashboard view
$user_role = normalize_role($_SESSION['role'] ?? 'Customer');

if ($user_role === 'Administrator') {
    require_once __DIR__ . '/views/admin/dashboard.php';
} elseif ($user_role === 'Restaurant Manager') {
    require_once __DIR__ . '/views/manager/dashboard.php';
} elseif ($user_role === 'Rider') {
    require_once __DIR__ . '/views/rider/dashboard.php';
} else {
    // Default to Customer
    require_once __DIR__ . '/views/customer/dashboard.php';
}
