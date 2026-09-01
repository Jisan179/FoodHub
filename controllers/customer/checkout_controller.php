<?php
/**
 * FoodHub - Customer Checkout Controller
 */

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';
require_once __DIR__ . '/../../models/user_model.php';

$customer_id = intval($_SESSION['user_id']);
$cart_summary = get_cart_summary($conn, $customer_id);

if ($cart_summary['total_items'] === 0) {
    header("Location: cart.php?error=Your+cart+is+empty");
    exit();
}

$user = find_user_by_id($conn, $customer_id);

$default_address = $user['address'] ?? '';
$default_phone   = $user['phone'] ?? '';

$currentPage = 'checkout';
