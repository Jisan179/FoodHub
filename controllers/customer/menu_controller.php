<?php
/**
 * FoodHub - Customer Restaurant Menu Controller
 */

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id   = intval($_SESSION['user_id']);
$restaurant_id = intval($_GET['restaurant_id'] ?? 0);
$category      = trim($_GET['category'] ?? 'All');

if ($restaurant_id <= 0) {
    header("Location: browse_restaurants.php");
    exit();
}

$restaurant = get_customer_restaurant_by_id($conn, $restaurant_id, $customer_id);

if (!$restaurant) {
    header("Location: browse_restaurants.php?error=Restaurant+not+found");
    exit();
}

$categories = get_restaurant_categories($conn, $restaurant_id);
$menu_items = get_customer_menu_items($conn, $restaurant_id, $category);
$is_favorited = is_restaurant_favorited($conn, $customer_id, $restaurant_id);

$currentPage = 'menu';
