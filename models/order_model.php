<?php
/**
 * FoodHub - Procedural Order Model
 * Pure procedural functions for orders table
 */

/**
 * Get all customer orders with customer, restaurant, delivery, and rider joins
 */
function get_all_orders($conn) {
    $sql = "
        SELECT 
            o.order_id,  
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            o.created_at,
            u.user_id AS customer_id,
            u.name AS customer_name,
            u.phone AS customer_phone,
            u.email AS customer_email,
            r.restaurant_id,
            r.name AS restaurant_name,
            d.delivery_id,
            COALESCE(d.delivery_status, 'Pending Assignment') AS delivery_status,
            rider.user_id AS rider_id,
            COALESCE(rider.name, 'Unassigned') AS rider_name,
            rider.phone AS rider_phone
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        LEFT JOIN users rider ON d.rider_id = rider.user_id
        ORDER BY o.order_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $orders = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }

    return $orders;
}

/**
 * Get recent orders for dashboard display
 */
function get_recent_orders($conn, $limit = 5) {
    $safe_limit = intval($limit);
    $sql = "
        SELECT 
            o.order_id,
            u.name AS customer_name,
            r.name AS restaurant_name,
            o.total_amount,
            o.order_status,
            o.created_at
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        ORDER BY o.order_id DESC
        LIMIT $safe_limit
    ";

    $result = mysqli_query($conn, $sql);
    $recent = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent[] = $row;
        }
    }

    return $recent;
}

/**
 * Count total orders across all time
 */
function count_total_orders($conn) {
    $sql = "SELECT COUNT(*) AS total FROM orders";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['total'] ?? 0);
    }
    return 0;
}

/**
 * Calculate total revenue excluding cancelled orders
 */
function get_total_revenue($conn) {
    $sql = "SELECT SUM(total_amount) AS total_revenue FROM orders WHERE order_status != 'Cancelled'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return floatval($row['total_revenue'] ?? 0.0);
    }
    return 0.0;
}

/**
 * Update the status of an order
 */
function update_order_status($conn, $order_id, $status) {
    $allowed_statuses = [
        'Pending',
        'Preparing',
        'Ready for Delivery',
        'Out for Delivery',
        'Delivered',
        'Cancelled'
    ];

    if (!in_array($status, $allowed_statuses, true)) {
        return false;
    }

    $safe_id = intval($order_id);
    $safe_status = mysqli_real_escape_string($conn, trim($status));

    $sql = "UPDATE orders SET order_status = '$safe_status' WHERE order_id = $safe_id";
    return (bool)mysqli_query($conn, $sql);
}
