<?php
/**
 * Action: Add Item to Cart
 */

require_once __DIR__ . '/../../controllers/auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id       = intval($_SESSION['user_id']);
$item_id           = intval($_POST['item_id'] ?? 0);
$quantity          = max(1, intval($_POST['quantity'] ?? 1));
$clear_if_conflict = isset($_POST['clear_if_conflict']) && $_POST['clear_if_conflict'] === '1';
$redirect_url      = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? '../cart.php';

if ($item_id <= 0) {
    $_SESSION['flash_error'] = "Invalid item selected.";
    $safe_redirect = resolve_customer_redirect($redirect_url, '../cart.php');
    header("Location: " . $safe_redirect);
    exit();
}

$result = add_to_cart($conn, $customer_id, $item_id, $quantity, $clear_if_conflict);

if ($result['status'] === 'success') {
    $_SESSION['flash_success'] = $result['message'];
} elseif ($result['status'] === 'conflict') {
    $_SESSION['cart_conflict'] = [
        'item_id'                => $item_id,
        'quantity'               => $quantity,
        'cart_restaurant_name'   => $result['cart_restaurant_name'],
        'new_restaurant_name'    => $result['new_restaurant_name']
    ];
} else {
    $_SESSION['flash_error'] = $result['message'] ?? 'Failed to add item to cart.';
}

$safe_redirect = resolve_customer_redirect($redirect_url, '../cart.php');
header("Location: " . $safe_redirect);
exit();
