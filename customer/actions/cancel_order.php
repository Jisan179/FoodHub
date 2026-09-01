<?php
/**
 * Action: Cancel Customer Order
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$order_id    = intval($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
$redirect_url = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? ('../order_track.php?order_id=' . $order_id);

if ($order_id <= 0) {
    $_SESSION['flash_error'] = "Invalid order ID specified.";
    header("Location: ../order_history.php");
    exit();
}

$cancel_res = cancel_customer_order($conn, $order_id, $customer_id);

if ($cancel_res['success']) {
    $_SESSION['flash_success'] = "Order #$order_id has been successfully cancelled.";
} else {
    $_SESSION['flash_error'] = $cancel_res['message'] ?? "Could not cancel this order.";
}

header("Location: " . $redirect_url);
exit();
