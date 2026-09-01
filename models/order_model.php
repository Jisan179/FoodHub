<?php
/**
 * FoodHub - Order Model
 * Procedural MySQLi Helpers with Prepared Statements
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
 * Get all orders placed by a specific customer
 */
function get_orders_by_customer_id($conn, $customer_id) {
    $customer_id = intval($customer_id);
    $stmt = mysqli_prepare($conn, "
        SELECT 
            o.order_id,
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            o.created_at,
            r.restaurant_id,
            r.name AS restaurant_name,
            r.phone AS restaurant_phone,
            d.delivery_status,
            rider.name AS rider_name,
            rider.phone AS rider_phone
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        LEFT JOIN users rider ON d.rider_id = rider.user_id
        WHERE o.customer_id = ?
        ORDER BY o.order_id DESC
    ");
    if (!$stmt) return [];

    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $orders;
}

/**
 * Get statistical summary for a customer
 */
function get_customer_order_stats($conn, $customer_id) {
    $customer_id = intval($customer_id);
    $stmt = mysqli_prepare($conn, "
        SELECT 
            COUNT(*) AS total_orders,
            SUM(CASE WHEN order_status IN ('Pending', 'Preparing', 'Ready for Delivery', 'Out for Delivery') THEN 1 ELSE 0 END) AS active_orders,
            SUM(CASE WHEN order_status = 'Delivered' THEN 1 ELSE 0 END) AS completed_orders,
            COALESCE(SUM(CASE WHEN order_status != 'Cancelled' THEN total_amount ELSE 0 END), 0) AS total_spent
        FROM orders 
        WHERE customer_id = ?
    ");
    if (!$stmt) {
        return ['total_orders' => 0, 'active_orders' => 0, 'completed_orders' => 0, 'total_spent' => 0.0];
    }

    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : [];
    mysqli_stmt_close($stmt);

    return [
        'total_orders'     => intval($stats['total_orders'] ?? 0),
        'active_orders'    => intval($stats['active_orders'] ?? 0),
        'completed_orders' => intval($stats['completed_orders'] ?? 0),
        'total_spent'      => floatval($stats['total_spent'] ?? 0.0)
    ];
}

/**
 * Get orders for a specific restaurant (Restaurant Manager view)
 */
function get_orders_by_restaurant_id($conn, $restaurant_id) {
    $restaurant_id = intval($restaurant_id);
    $stmt = mysqli_prepare($conn, "
        SELECT 
            o.order_id,
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            o.created_at,
            u.name AS customer_name,
            u.phone AS customer_phone,
            d.delivery_status,
            rider.name AS rider_name,
            rider.phone AS rider_phone
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        LEFT JOIN users rider ON d.rider_id = rider.user_id
        WHERE o.restaurant_id = ?
        ORDER BY o.order_id DESC
    ");
    if (!$stmt) return [];

    mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $orders;
}

/**
 * Get manager dashboard statistics
 */
function get_manager_stats($conn, $restaurant_id) {
    $restaurant_id = intval($restaurant_id);
    
    // Order stats
    $stmt = mysqli_prepare($conn, "
        SELECT 
            COUNT(*) AS total_orders,
            SUM(CASE WHEN order_status IN ('Pending', 'Preparing', 'Ready for Delivery') THEN 1 ELSE 0 END) AS incoming_orders,
            SUM(CASE WHEN order_status = 'Delivered' THEN 1 ELSE 0 END) AS completed_orders,
            COALESCE(SUM(CASE WHEN order_status != 'Cancelled' THEN total_amount ELSE 0 END), 0) AS total_revenue
        FROM orders 
        WHERE restaurant_id = ?
    ");
    
    $stats = ['total_orders' => 0, 'incoming_orders' => 0, 'completed_orders' => 0, 'total_revenue' => 0.0, 'total_items' => 0];

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $stats['total_orders']     = intval($row['total_orders'] ?? 0);
            $stats['incoming_orders']  = intval($row['incoming_orders'] ?? 0);
            $stats['completed_orders'] = intval($row['completed_orders'] ?? 0);
            $stats['total_revenue']    = floatval($row['total_revenue'] ?? 0.0);
        }
        mysqli_stmt_close($stmt);
    }

    // Food items count
    $stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) AS total_items FROM food_items WHERE restaurant_id = ?");
    if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, "i", $restaurant_id);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);
        if ($res2 && $row2 = mysqli_fetch_assoc($res2)) {
            $stats['total_items'] = intval($row2['total_items'] ?? 0);
        }
        mysqli_stmt_close($stmt2);
    }

    return $stats;
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

    $id = intval($order_id);
    $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE order_id = ?");
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}
