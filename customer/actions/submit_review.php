<?php
/**
 * Action: Submit Food Review for Delivered Order Item
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id  = intval($_SESSION['user_id']);
$order_id     = intval($_POST['order_id'] ?? 0);
$item_id      = intval($_POST['item_id'] ?? 0);
$rating       = intval($_POST['rating'] ?? 5);
$comment      = trim($_POST['comment'] ?? '');
$redirect_url = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? '../reviews.php';

if ($order_id <= 0 || $item_id <= 0) {
    $_SESSION['flash_error'] = "Invalid order item specified for review.";
    $safe_redirect = resolve_customer_redirect($redirect_url, '../reviews.php');
    header("Location: " . $safe_redirect);
    exit();
}

$review_res = submit_food_review($conn, $customer_id, $order_id, $item_id, $rating, $comment);

if ($review_res['success']) {
    $_SESSION['flash_success'] = "Thank you! Your review has been submitted.";
} else {
    $_SESSION['flash_error'] = $review_res['message'] ?? "Could not submit review.";
}

$safe_redirect = resolve_customer_redirect($redirect_url, '../reviews.php');
header("Location: " . $safe_redirect);
exit();
