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

    $delivery_statuses = ['Pending Assignment', 'Assigned', 'Picked Up', 'Delivered', 'Cancelled'];
    $rider_ids = array_map('intval', array_column(get_active_riders($conn), 'user_id'));
    $current = $order_id > 0 ? get_delivery_by_order_id($conn, $order_id) : null;
    $rider_changed = $rider_id !== null && $rider_id !== intval($current['rider_id'] ?? 0);

    // A cancelled order always cancels its delivery too
    if ($order_status === 'Cancelled') {
        $delivery_status = 'Cancelled';
    }

    if ($order_id <= 0 || empty($order_status)) {
        $error = "Invalid order parameters provided.";
    } elseif ($delivery_status !== '' && !in_array($delivery_status, $delivery_statuses, true)) {
        $error = "Invalid delivery status provided.";
    } elseif ($rider_changed && !in_array($rider_id, $rider_ids, true)) {
        $error = "The selected user is not an active rider.";
    } elseif ($rider_id === null && in_array($delivery_status, ['Assigned', 'Picked Up', 'Delivered'], true)) {
        $error = "Select a rider before setting the delivery to '$delivery_status'.";
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
