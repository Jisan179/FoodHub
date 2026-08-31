<?php
/**
 * FoodHub - Procedural Admin Order Controller
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/order_model.php';
require_once __DIR__ . '/../../models/delivery_model.php';
require_once __DIR__ . '/../../models/user_model.php';

$error = "";
$success = "";

// 1. Handle Order & Delivery Status / Rider Assignment (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $order_id        = intval($_POST['order_id'] ?? 0);
    $order_status    = trim($_POST['order_status'] ?? '');
    $delivery_status = trim($_POST['delivery_status'] ?? '');
    $rider_id        = !empty($_POST['rider_id']) ? intval($_POST['rider_id']) : null;

    if ($order_id <= 0 || empty($order_status)) {
        $error = "Invalid order parameters provided.";
    } else {
        $order_updated = update_order_status($conn, $order_id, $order_status);

        if ($order_updated) {
            if (!empty($delivery_status)) {
                upsert_delivery($conn, $order_id, $rider_id, $delivery_status);
            }
            $success = "Order #$order_id and delivery details were successfully updated.";
        } else {
            $error = "Failed to update order: " . mysqli_error($conn);
        }
    }
}

// 2. Fetch Available Riders for Assignment Dropdown
$riders = get_active_riders($conn);

// 3. Fetch Orders with Customer, Restaurant, Delivery & Rider Joins
$orders = get_all_orders($conn);
$currentPage = 'orders';
