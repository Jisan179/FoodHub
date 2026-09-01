<?php
/**
 * Action: Remove Item from Cart
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$item_id     = intval($_POST['item_id'] ?? $_GET['item_id'] ?? 0);

if ($item_id > 0) {
    remove_from_cart($conn, $customer_id, $item_id);
    $_SESSION['flash_success'] = "Item removed from cart.";
}

header("Location: ../cart.php");
exit();
