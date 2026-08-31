<?php
/**
 * FoodHub - Procedural Delivery Model
 * Pure procedural functions for deliveries table
 */

/**
 * Get delivery details by order ID
 */
function get_delivery_by_order_id($conn, $order_id) {
    $safe_id = intval($order_id);
    $sql = "SELECT * FROM deliveries WHERE order_id = $safe_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Create or update delivery record for an order
 */
function upsert_delivery($conn, $order_id, $rider_id, $delivery_status) {
    $allowed_statuses = [
        'Pending Assignment',
        'Assigned',
        'Picked Up',
        'Delivered',
        'Cancelled'
    ];

    if (!in_array($delivery_status, $allowed_statuses, true)) {
        return false;
    }

    $safe_order_id = intval($order_id);
    $safe_status = mysqli_real_escape_string($conn, trim($delivery_status));
    $rider_val = ($rider_id !== null && intval($rider_id) > 0) ? intval($rider_id) : "NULL";

    $existing = get_delivery_by_order_id($conn, $safe_order_id);

    if ($existing) {
        $sql = "
            UPDATE deliveries 
            SET delivery_status = '$safe_status', 
                rider_id = $rider_val
            WHERE order_id = $safe_order_id
        ";
    } else {
        $sql = "
            INSERT INTO deliveries (order_id, rider_id, delivery_status, assigned_at) 
            VALUES ($safe_order_id, $rider_val, '$safe_status', NOW())
        ";
    }

    return (bool)mysqli_query($conn, $sql);
}
