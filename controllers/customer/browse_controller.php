<?php
/**
 * FoodHub - Customer Browse Restaurants & Food Controller
 */

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$search_query = trim($_GET['search'] ?? '');

$restaurants = get_customer_approved_restaurants($conn, $search_query, $customer_id);

// If search term provided, also search matching food items
$food_search_results = [];
if (!empty($search_query)) {
    $food_search_results = search_food_and_restaurants($conn, $search_query);
}

$currentPage = 'browse';
