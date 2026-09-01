<?php
/**
 * Action: Update Cart Item Quantity
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$item_id     = intval($_POST['item_id'] ?? 0);
$quantity    = intval($_POST['quantity'] ?? 0);

if ($item_id > 0) {
    update_cart_quantity($conn, $customer_id, $item_id, $quantity);
    $_SESSION['flash_success'] = "Cart updated successfully.";
}

header("Location: ../cart.php");
exit();
