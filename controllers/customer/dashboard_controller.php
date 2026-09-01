<?php
/**
 * FoodHub - Customer Dashboard Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';
require_once __DIR__ . '/../../models/order_model.php';
require_once __DIR__ . '/../../models/restaurant_model.php';

$customer_id = intval($_SESSION['user_id']);
$customer_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Customer';

// Fetch metrics & stats
$customer_stats = get_customer_order_stats($conn, $customer_id);
$all_orders     = get_customer_orders($conn, $customer_id);
$total_orders   = count($all_orders);

$active_orders = array_values(array_filter($all_orders, function($o) {
    return in_array($o['order_status'], ['Pending', 'Preparing', 'Ready for Delivery', 'Out for Delivery'], true);
}));

$favorites        = get_customer_favorites($conn, $customer_id);
$total_favorites  = count($favorites);
$reviews          = get_customer_reviews($conn, $customer_id);
$total_reviews    = count($reviews);

// Featured restaurants
$featured_restaurants = get_customer_approved_restaurants($conn, null, $customer_id);
$featured_restaurants = array_slice($featured_restaurants, 0, 4);

// Unreviewed items waiting for review
$unreviewed_items = get_unreviewed_delivered_items($conn, $customer_id);
$my_orders = $all_orders;

$currentPage = 'dashboard';
