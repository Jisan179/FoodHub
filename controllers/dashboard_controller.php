<?php
/**
 * FoodHub - Unified Role-Based Dashboard Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/restaurant_model.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../models/delivery_model.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Enforce authentication
check_auth();

$user_id = intval($_SESSION['user_id']);
$user_role = normalize_role($_SESSION['role'] ?? 'Customer');
$user_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'User';

$error = "";
$success = "";

if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// ----------------------------------------------------
// ROLE 1: CUSTOMER DASHBOARD LOGIC
// ----------------------------------------------------
if ($user_role === 'Customer') {
    require_once __DIR__ . '/../models/customer_model.php';

    $customer_id = $user_id;
    $customer_name = $user_name;

    $customer_stats = get_customer_order_stats($conn, $customer_id);
    $all_orders = get_customer_orders($conn, $customer_id);
    $total_orders = count($all_orders);
    
    $active_orders = array_values(array_filter($all_orders, function($o) {
        return in_array($o['order_status'], ['Pending', 'Preparing', 'Ready for Delivery', 'Out for Delivery'], true);
    }));

    $favorites = get_customer_favorites($conn, $customer_id);
    $total_favorites = count($favorites);

    $reviews = get_customer_reviews($conn, $customer_id);
    $total_reviews = count($reviews);

    $featured_restaurants = get_customer_approved_restaurants($conn, null, $customer_id);
    $featured_restaurants = array_slice($featured_restaurants, 0, 4);

    $unreviewed_items = get_unreviewed_delivered_items($conn, $customer_id);
    $my_orders = $all_orders;
    $partner_restaurants = get_approved_restaurants($conn, 6);
}

// ----------------------------------------------------
// ROLE 2: RESTAURANT MANAGER DASHBOARD LOGIC
// ----------------------------------------------------
elseif ($user_role === 'Restaurant Manager') {
    $my_restaurant = get_restaurant_by_manager_id($conn, $user_id);

    // Order status changes are handled only by manager/controllers/order_controller.php,
    // which verifies restaurant ownership.

    if ($my_restaurant) {
        $restaurant_id = intval($my_restaurant['restaurant_id']);
        $manager_stats = get_manager_stats($conn, $restaurant_id);
        $restaurant_orders = get_orders_by_restaurant_id($conn, $restaurant_id);
        $menu_items = get_food_items_by_restaurant($conn, $restaurant_id);
    } else {
        $manager_stats = ['total_orders' => 0, 'incoming_orders' => 0, 'completed_orders' => 0, 'total_revenue' => 0.0, 'total_items' => 0];
        $restaurant_orders = [];
        $menu_items = [];
    }
}

// ----------------------------------------------------
// ROLE 3: RIDER DASHBOARD LOGIC
// ----------------------------------------------------
elseif ($user_role === 'Rider') {
    require_once __DIR__ . '/../models/rider_model.php';

    // Both actions go through rider_update_delivery(), which locks the row,
    // checks ownership and only allows valid status transitions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['claim_delivery', 'update_delivery_status'], true)) {
        if ($_POST['action'] === 'claim_delivery') {
            $delivery = get_delivery_by_order_id($conn, intval($_POST['order_id'] ?? 0));
            $delivery_id = intval($delivery['delivery_id'] ?? 0);
            $rider_action = 'accept';
        } else {
            $delivery_id = intval($_POST['delivery_id'] ?? 0);
            $status_actions = ['Picked Up' => 'pickup', 'Delivered' => 'deliver'];
            $rider_action = $status_actions[$_POST['status'] ?? ''] ?? '';
        }

        $result = rider_update_delivery($conn, $delivery_id, $user_id, $rider_action);
        if ($result['ok']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }

    $rider_stats = get_rider_stats($conn, $user_id);
    $available_deliveries = get_available_deliveries($conn);
    $my_deliveries = get_deliveries_by_rider_id($conn, $user_id);
}

// ----------------------------------------------------
// ROLE 4: ADMINISTRATOR LOGIC
// ----------------------------------------------------
elseif ($user_role === 'Administrator') {
    require_once __DIR__ . '/admin/dashboard_controller.php';
}
