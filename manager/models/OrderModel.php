<?php
// manager/models/OrderModel.php

/**
 * Get all orders for a list of restaurants.
 */
function getOrdersByRestaurants($conn, $restaurant_ids) {
    if (empty($restaurant_ids)) {
        return [];
    }
    
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($restaurant_ids), '?'));
    $types = str_repeat('i', count($restaurant_ids));
    
    $sql = "
        SELECT o.*, u.name as customer_name, u.phone as customer_phone, r.name as restaurant_name 
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        WHERE o.restaurant_id IN ($placeholders)
        ORDER BY o.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$restaurant_ids);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get detailed information for a specific order.
 */
function getOrderByIdAndRestaurants($conn, $order_id, $restaurant_ids) {
    if (empty($restaurant_ids)) {
        return false;
    }
    
    $placeholders = implode(',', array_fill(0, count($restaurant_ids), '?'));
    $types = 'i' . str_repeat('i', count($restaurant_ids));
    
    $sql = "
        SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.address as customer_address, r.name as restaurant_name 
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        WHERE o.order_id = ? AND o.restaurant_id IN ($placeholders)
    ";
    
    $stmt = $conn->prepare($sql);
    $params = array_merge([$order_id], $restaurant_ids);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get items for a specific order.
 */
function getOrderItems($conn, $order_id) {
    $stmt = $conn->prepare("
        SELECT oi.*, f.name as item_name 
        FROM order_items oi
        JOIN food_items f ON oi.item_id = f.item_id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Log order status change.
 */
function logOrderStatusChange($conn, $order_id, $old_status, $new_status, $user_id) {
    $stmt = $conn->prepare("
        INSERT INTO order_status_log (order_id, old_status, new_status, changed_by) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("issi", $order_id, $old_status, $new_status, $user_id);
    return $stmt->execute();
}

/**
 * Update order status with validation.
 * Allowed transitions (manager): 
 * 'Pending' -> 'Preparing' (Accept)
 * 'Pending' -> 'Cancelled' (Reject)
 * 'Preparing' -> 'Ready for Delivery'
 */
function updateOrderStatus($conn, $order_id, $new_status, $restaurant_ids, $user_id) {
    // Verify ownership and get current order
    $order = getOrderByIdAndRestaurants($conn, $order_id, $restaurant_ids);
    if (!$order) {
        return ['success' => false, 'message' => 'Order not found or unauthorized.'];
    }
    
    $old_status = $order['order_status'];
    
    // Validate state transitions
    $valid = false;
    if ($old_status === 'Pending' && in_array($new_status, ['Preparing', 'Cancelled'])) {
        $valid = true;
    } elseif ($old_status === 'Preparing' && $new_status === 'Ready for Delivery') {
        $valid = true;
    }
    
    if (!$valid) {
        return ['success' => false, 'message' => "Invalid status transition from $old_status to $new_status."];
    }
    
    $conn->begin_transaction();
    try {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        
        // Log the change
        logOrderStatusChange($conn, $order_id, $old_status, $new_status, $user_id);
        
        $conn->commit();
        return ['success' => true, 'message' => 'Order status updated successfully.'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Database error occurred.'];
    }
}
