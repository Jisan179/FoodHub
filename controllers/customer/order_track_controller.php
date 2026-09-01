<?php
/**
 * FoodHub - Customer Order Tracking Controller
 */

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$order_id    = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: order_history.php");
    exit();
}

$order = get_customer_order_details($conn, $order_id, $customer_id);

if (!$order) {
    header("Location: order_history.php?error=Order+not+found");
    exit();
}

$can_cancel = in_array($order['order_status'], ['Pending', 'Preparing'], true);
$currentPage = 'track';
