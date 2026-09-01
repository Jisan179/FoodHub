<?php
/**
 * FoodHub - Customer Reviews Controller
 */

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$my_reviews  = get_customer_reviews($conn, $customer_id);
$pending_review_items = get_unreviewed_delivered_items($conn, $customer_id);

$currentPage = 'reviews';
