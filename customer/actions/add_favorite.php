<?php
/**
 * Action: Add Restaurant to Favorites
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id   = intval($_SESSION['user_id']);
$restaurant_id = intval($_POST['restaurant_id'] ?? $_GET['restaurant_id'] ?? 0);
$redirect_url  = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? '../browse_restaurants.php';

if ($restaurant_id > 0) {
    add_customer_favorite($conn, $customer_id, $restaurant_id);
    $_SESSION['flash_success'] = "Restaurant added to your favorites!";
}

header("Location: " . $redirect_url);
exit();
