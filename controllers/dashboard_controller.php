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

    // Handle order status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? '');

        if ($order_id > 0 && !empty($new_status)) {
            if (update_order_status($conn, $order_id, $new_status)) {
                $success = "Order #{$order_id} status updated to '{$new_status}' successfully!";
            } else {
                $error = "Failed to update order status.";
            }
        }
    }

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
    // Handle claim delivery action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'claim_delivery') {
        $order_id = intval($_POST['order_id'] ?? 0);
        if ($order_id > 0) {
            if (claim_delivery($conn, $order_id, $user_id)) {
                $success = "Delivery for Order #{$order_id} assigned to you! Safe riding!";
            } else {
                $error = "Unable to accept delivery. It may already be assigned.";
            }
        }
    }

    // Handle delivery status update action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_delivery_status') {
        $delivery_id = intval($_POST['delivery_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($delivery_id > 0 && !empty($status)) {
            if (update_delivery_status_by_id($conn, $delivery_id, $status)) {
                $success = "Delivery status successfully updated to '{$status}'!";
            } else {
                $error = "Failed to update delivery status.";
            }
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
