<?php
/**
 * Action: Delete Customer Review
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id  = intval($_SESSION['user_id']);
$review_id    = intval($_POST['review_id'] ?? $_GET['review_id'] ?? 0);
$redirect_url = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? '../reviews.php';

if ($review_id <= 0) {
    $_SESSION['flash_error'] = "Invalid review specified.";
    $safe_redirect = resolve_customer_redirect($redirect_url, '../reviews.php');
    header("Location: " . $safe_redirect);
    exit();
}

$deleted = delete_food_review($conn, $review_id, $customer_id);

if ($deleted) {
    $_SESSION['flash_success'] = "Review deleted successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to delete review.";
}

$safe_redirect = resolve_customer_redirect($redirect_url, '../reviews.php');
header("Location: " . $safe_redirect);
exit();
