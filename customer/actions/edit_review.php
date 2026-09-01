<?php
/**
 * Action: Edit Customer Review
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id  = intval($_SESSION['user_id']);
$review_id    = intval($_POST['review_id'] ?? 0);
$rating       = intval($_POST['rating'] ?? 5);
$comment      = trim($_POST['comment'] ?? '');
$redirect_url = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? '../reviews.php';

if ($review_id <= 0) {
    $_SESSION['flash_error'] = "Invalid review specified.";
    header("Location: " . $redirect_url);
    exit();
}

$updated = update_food_review($conn, $review_id, $customer_id, $rating, $comment);

if ($updated) {
    $_SESSION['flash_success'] = "Your review was updated successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to update review.";
}

header("Location: " . $redirect_url);
exit();
