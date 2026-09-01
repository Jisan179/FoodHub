<?php
/**
 * FoodHub - Delivery Model
 * Procedural MySQLi Helpers with Prepared Statements
 */

/**
 * Get delivery details by order ID
 */
function get_delivery_by_order_id($conn, $order_id) {
    $order_id = intval($order_id);
    $stmt = mysqli_prepare($conn, "SELECT * FROM deliveries WHERE order_id = ? LIMIT 1");
    if (!$stmt) return null;

    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $delivery = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $delivery;
}

/**
 * Get deliveries available for riders to pick up
 */
function get_available_deliveries($conn) {
    $sql = "
        SELECT 
            o.order_id,
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.created_at,
            r.name AS restaurant_name,
            r.address AS restaurant_address,
            r.phone AS restaurant_phone,
            u.name AS customer_name,
            u.phone AS customer_phone,
            d.delivery_id,
            COALESCE(d.delivery_status, 'Pending Assignment') AS delivery_status
        FROM orders o
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        JOIN users u ON o.customer_id = u.user_id
        LEFT JOIN deliveries d ON o.order_id = d.order_id
        WHERE (d.rider_id IS NULL OR d.delivery_status = 'Pending Assignment')
          AND o.order_status IN ('Pending', 'Preparing', 'Ready for Delivery')
        ORDER BY o.order_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $deliveries = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $deliveries[] = $row;
        }
    }

    return $deliveries;
}

/**
 * Get deliveries assigned to a specific rider
 */
function get_deliveries_by_rider_id($conn, $rider_id) {
    $rider_id = intval($rider_id);
    $stmt = mysqli_prepare($conn, "
        SELECT 
            d.delivery_id,
            d.delivery_status,
            d.assigned_at,
            d.delivered_at,
            o.order_id,
            o.total_amount,
            o.order_status,
            o.delivery_address,
            o.payment_method,
            o.payment_status,
            r.name AS restaurant_name,
            r.address AS restaurant_address,
            r.phone AS restaurant_phone,
            u.name AS customer_name,
            u.phone AS customer_phone
        FROM deliveries d
        JOIN orders o ON d.order_id = o.order_id
        JOIN restaurants r ON o.restaurant_id = r.restaurant_id
        JOIN users u ON o.customer_id = u.user_id
        WHERE d.rider_id = ?
        ORDER BY d.delivery_id DESC
    ");
    if (!$stmt) return [];

    mysqli_stmt_bind_param($stmt, "i", $rider_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $deliveries = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $deliveries[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $deliveries;
}

/**
 * Get statistical summary for a rider
 */
function get_rider_stats($conn, $rider_id) {
    $rider_id = intval($rider_id);
    $stmt = mysqli_prepare($conn, "
        SELECT 
            COUNT(*) AS total_assigned,
            SUM(CASE WHEN delivery_status IN ('Assigned', 'Picked Up') THEN 1 ELSE 0 END) AS active_deliveries,
            SUM(CASE WHEN delivery_status = 'Delivered' THEN 1 ELSE 0 END) AS completed_deliveries
        FROM deliveries
        WHERE rider_id = ?
    ");
    
    $stats = ['total_assigned' => 0, 'total_deliveries' => 0, 'active_deliveries' => 0, 'completed_deliveries' => 0, 'total_earnings' => 0.0];

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $rider_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $stats['total_assigned']       = intval($row['total_assigned'] ?? 0);
            $stats['total_deliveries']     = intval($row['total_assigned'] ?? 0);
            $stats['active_deliveries']    = intval($row['active_deliveries'] ?? 0);
            $stats['completed_deliveries'] = intval($row['completed_deliveries'] ?? 0);
            // Example flat ৳50 / $3 commission per completed delivery
            $stats['total_earnings']       = $stats['completed_deliveries'] * 3.50;
        }
        mysqli_stmt_close($stmt);
    }

    return $stats;
}

/**
 * Rider claims / accepts an available delivery
 */
function claim_delivery($conn, $order_id, $rider_id) {
    $order_id = intval($order_id);
    $rider_id = intval($rider_id);

    $existing = get_delivery_by_order_id($conn, $order_id);

    if ($existing) {
        $stmt = mysqli_prepare($conn, "
            UPDATE deliveries 
            SET rider_id = ?, delivery_status = 'Assigned', assigned_at = NOW() 
            WHERE order_id = ?
        ");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "ii", $rider_id, $order_id);
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO deliveries (order_id, rider_id, delivery_status, assigned_at) 
            VALUES (?, ?, 'Assigned', NOW())
        ");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "ii", $order_id, $rider_id);
    }

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Also update order status to Out for Delivery if ready
    if ($success) {
        $update_ord = mysqli_prepare($conn, "UPDATE orders SET order_status = 'Out for Delivery' WHERE order_id = ?");
        if ($update_ord) {
            mysqli_stmt_bind_param($update_ord, "i", $order_id);
            mysqli_stmt_execute($update_ord);
            mysqli_stmt_close($update_ord);
        }
    }

    return $success;
}

/**
 * Update delivery status (Picked Up, Delivered, etc.)
 */
function update_delivery_status_by_id($conn, $delivery_id, $status) {
    $delivery_id = intval($delivery_id);
    $allowed = ['Pending Assignment', 'Assigned', 'Picked Up', 'Delivered', 'Cancelled'];
    if (!in_array($status, $allowed, true)) return false;

    if ($status === 'Delivered') {
        $stmt = mysqli_prepare($conn, "UPDATE deliveries SET delivery_status = ?, delivered_at = NOW() WHERE delivery_id = ?");
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE deliveries SET delivery_status = ? WHERE delivery_id = ?");
    }

    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "si", $status, $delivery_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // If delivered, update the order status and payment status if COD
    if ($success && $status === 'Delivered') {
        $stmt2 = mysqli_prepare($conn, "
            UPDATE orders o 
            JOIN deliveries d ON o.order_id = d.order_id 
            SET o.order_status = 'Delivered', o.payment_status = 'Paid' 
            WHERE d.delivery_id = ?
        ");
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, "i", $delivery_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        }
    }

    return $success;
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

    $order_id = intval($order_id);
    $existing = get_delivery_by_order_id($conn, $order_id);

    if ($existing) {
        $stmt = mysqli_prepare($conn, "
            UPDATE deliveries 
            SET delivery_status = ?, rider_id = ? 
            WHERE order_id = ?
        ");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "sii", $delivery_status, $rider_id, $order_id);
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO deliveries (order_id, rider_id, delivery_status, assigned_at) 
            VALUES (?, ?, ?, NOW())
        ");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "iis", $order_id, $rider_id, $delivery_status);
    }

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}
