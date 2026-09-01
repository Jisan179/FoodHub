<?php
/**
 * Action: Place Customer Order
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id      = intval($_SESSION['user_id']);
$delivery_address = trim($_POST['delivery_address'] ?? '');
$payment_method   = trim($_POST['payment_method'] ?? 'Cash on Delivery');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../checkout.php");
    exit();
}

if (empty($delivery_address)) {
    $_SESSION['flash_error'] = "Please provide your delivery address.";
    header("Location: ../checkout.php");
    exit();
}

$order_result = place_customer_order($conn, $customer_id, $delivery_address, $payment_method);

if ($order_result['success']) {
    $new_order_id = $order_result['order_id'];
    $_SESSION['flash_success'] = "🎉 Your order #$new_order_id has been placed successfully!";
    header("Location: ../order_track.php?order_id=" . $new_order_id);
    exit();
} else {
    $_SESSION['flash_error'] = $order_result['error'] ?? "Failed to place order.";
    header("Location: ../checkout.php");
    exit();
}
