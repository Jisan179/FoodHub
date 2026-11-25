<?php
/**
 * FoodHub - Procedural Rider Delivery Model
 */

function get_rider_deliveries($conn, $rider_id, $history = false) {
    $rider_id = intval($rider_id);
    $status_filter = $history ? "d.rider_id = $rider_id AND d.delivery_status IN ('Delivered', 'Cancelled')" : "(d.rider_id = $rider_id OR (d.rider_id IS NULL AND d.delivery_status = 'Pending Assignment')) AND d.delivery_status NOT IN ('Delivered', 'Cancelled')";
    $sql = "
        SELECT d.delivery_id, d.order_id, d.rider_id, d.delivery_status, d.assigned_at,
               d.pickup_time, d.delivered_at, d.rider_earning, d.rider_note, d.updated_at,
               o.order_status, o.total_amount, o.delivery_address, o.created_at,
               c.name AS customer_name, c.phone AS customer_phone,
               r.name AS restaurant_name, r.address AS restaurant_address
        FROM deliveries d
        JOIN orders o ON o.order_id = d.order_id
        JOIN users c ON c.user_id = o.customer_id
        JOIN restaurants r ON r.restaurant_id = o.restaurant_id
        WHERE $status_filter
        ORDER BY d.updated_at DESC, d.delivery_id DESC
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

function get_rider_summary($conn, $rider_id) {
    $rider_id = intval($rider_id);
    $sql = "SELECT
                (SELECT COUNT(*) FROM deliveries WHERE (rider_id = $rider_id OR (rider_id IS NULL AND delivery_status = 'Pending Assignment')) AND delivery_status NOT IN ('Delivered', 'Cancelled')) AS active_count,
                (SELECT COUNT(*) FROM deliveries WHERE rider_id = $rider_id AND delivery_status IN ('Delivered', 'Cancelled')) AS history_count,
                (SELECT COALESCE(SUM(rider_earning), 0) FROM deliveries WHERE rider_id = $rider_id AND delivery_status = 'Delivered') AS total_earnings";
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_assoc($result) : ['active_count' => 0, 'history_count' => 0, 'total_earnings' => 0];
}

function get_delivery_history($conn, $delivery_id, $rider_id) {
    $delivery_id = intval($delivery_id);
    $rider_id = intval($rider_id);
    $sql = "SELECT h.status, h.note, h.created_at, u.name AS actor_name
            FROM delivery_status_history h
            LEFT JOIN users u ON u.user_id = h.rider_id
            JOIN deliveries d ON d.delivery_id = h.delivery_id
            WHERE h.delivery_id = $delivery_id AND d.rider_id = $rider_id
            ORDER BY h.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $history = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
    }
    return $history;
}

function rider_update_delivery($conn, $delivery_id, $rider_id, $action, $note = '') {
    $delivery_id = intval($delivery_id);
    $rider_id = intval($rider_id);
    $note = trim($note);
    $actions = [
        'accept' => ['from' => ['Pending Assignment'], 'to' => 'Assigned'],
        'pickup' => ['from' => ['Assigned'], 'to' => 'Picked Up'],
        'deliver' => ['from' => ['Picked Up'], 'to' => 'Delivered'],
        'cancel' => ['from' => ['Assigned', 'Picked Up'], 'to' => 'Cancelled'],
        'update' => ['from' => ['Assigned', 'Picked Up'], 'to' => null]
    ];
    if (!isset($actions[$action]) || $delivery_id <= 0 || $rider_id <= 0 || strlen($note) > 500) {
        return ['ok' => false, 'message' => 'Invalid delivery action or note.'];
    }

    mysqli_begin_transaction($conn);
    $sql = "SELECT d.delivery_id, d.rider_id, d.delivery_status, o.order_status
            FROM deliveries d JOIN orders o ON o.order_id = d.order_id
            WHERE d.delivery_id = $delivery_id FOR UPDATE";
    $result = mysqli_query($conn, $sql);
    $delivery = $result ? mysqli_fetch_assoc($result) : null;
    $rule = $actions[$action];
    $can_claim = $action === 'accept' && $delivery && $delivery['rider_id'] === null && in_array($delivery['delivery_status'], $rule['from'], true);
    $owns_delivery = $delivery && intval($delivery['rider_id']) === $rider_id;
    if (!$delivery || (!$can_claim && (!$owns_delivery || !in_array($delivery['delivery_status'], $rule['from'], true)))) {
        mysqli_rollback($conn);
        return ['ok' => false, 'message' => 'This delivery is unavailable or is not assigned to you.'];
    }

    if ($action === 'accept' && in_array($delivery['order_status'], ['Cancelled', 'Delivered'], true)) {
        mysqli_rollback($conn);
        return ['ok' => false, 'message' => 'This order is already closed.'];
    }
    if ($action === 'pickup' && !in_array($delivery['order_status'], ['Ready for Delivery', 'Out for Delivery'], true)) {
        mysqli_rollback($conn);
        return ['ok' => false, 'message' => 'The restaurant has not marked this order Ready for Delivery yet.'];
    }

    $safe_note = mysqli_real_escape_string($conn, $note);
    if ($action === 'update') {
        $update_note = $note === '' ? 'NULL' : "'$safe_note'";
        if (!mysqli_query($conn, "UPDATE deliveries SET rider_note = $update_note WHERE delivery_id = $delivery_id")) {
            mysqli_rollback($conn);
            return ['ok' => false, 'message' => 'Unable to update delivery information.'];
        }
        $current_status = mysqli_real_escape_string($conn, $delivery['delivery_status']);
        if (!mysqli_query($conn, "INSERT INTO delivery_status_history (delivery_id, rider_id, status, note) VALUES ($delivery_id, $rider_id, '$current_status', " . ($note === '' ? 'NULL' : "'$safe_note'") . ')')) {
            mysqli_rollback($conn);
            return ['ok' => false, 'message' => 'Delivery history could not be recorded.'];
        }
        mysqli_commit($conn);
        return ['ok' => true, 'message' => 'Delivery information updated.'];
    }

    if ($action === 'cancel') {
        // A rider giving up a trip releases it back to the queue; the customer's order stays alive
        $note_sql = $note === '' ? 'NULL' : "'$safe_note'";
        $ok = mysqli_query($conn, "UPDATE deliveries SET rider_id = NULL, delivery_status = 'Pending Assignment', assigned_at = NULL, pickup_time = NULL, rider_note = $note_sql WHERE delivery_id = $delivery_id")
            && mysqli_query($conn, "INSERT INTO delivery_status_history (delivery_id, rider_id, status, note) VALUES ($delivery_id, $rider_id, 'Cancelled', $note_sql)")
            && mysqli_query($conn, "UPDATE orders o JOIN deliveries d ON d.order_id = o.order_id SET o.order_status = 'Ready for Delivery' WHERE d.delivery_id = $delivery_id AND o.order_status = 'Out for Delivery'");
        if (!$ok) {
            mysqli_rollback($conn);
            return ['ok' => false, 'message' => 'Unable to release the delivery.'];
        }
        mysqli_commit($conn);
        return ['ok' => true, 'message' => 'Delivery released. Another rider can now accept it.'];
    }

    $status = mysqli_real_escape_string($conn, $rule['to']);
    $rider_clause = $can_claim ? "rider_id = $rider_id, assigned_at = NOW()," : '';
    $time_clause = $rule['to'] === 'Picked Up' ? ', pickup_time = NOW()' : ($rule['to'] === 'Delivered' ? ', delivered_at = NOW()' : '');
    $earning_clause = $rule['to'] === 'Delivered' ? ', rider_earning = (SELECT ROUND(o.total_amount * 0.10, 2) FROM orders o JOIN deliveries d2 ON d2.order_id = o.order_id WHERE d2.delivery_id = ' . $delivery_id . ')' : '';
    $update = "UPDATE deliveries SET $rider_clause delivery_status = '$status', rider_note = " . ($note === '' ? 'NULL' : "'$safe_note'") . "$time_clause$earning_clause WHERE delivery_id = $delivery_id";
    if (!mysqli_query($conn, $update)) {
        mysqli_rollback($conn);
        return ['ok' => false, 'message' => 'Unable to update the delivery.'];
    }

    $history = "INSERT INTO delivery_status_history (delivery_id, rider_id, status, note) VALUES ($delivery_id, $rider_id, '$status', " . ($note === '' ? 'NULL' : "'$safe_note'") . ')';
    if (!mysqli_query($conn, $history)) {
        mysqli_rollback($conn);
        return ['ok' => false, 'message' => 'Delivery history could not be recorded. Import schema.sql first.'];
    }

    $order_status = $rule['to'] === 'Picked Up' ? 'Out for Delivery' : ($rule['to'] === 'Delivered' ? 'Delivered' : null);
    if ($order_status !== null) {
        $safe_order_status = mysqli_real_escape_string($conn, $order_status);
        // Cash is collected at the door, so a delivered order is also a paid order
        $paid_clause = $order_status === 'Delivered' ? ", o.payment_status = 'Paid'" : '';
        if (!mysqli_query($conn, "UPDATE orders o JOIN deliveries d ON d.order_id = o.order_id SET o.order_status = '$safe_order_status'$paid_clause WHERE d.delivery_id = $delivery_id")) {
            mysqli_rollback($conn);
            return ['ok' => false, 'message' => 'Unable to update the order status.'];
        }
    }
    mysqli_commit($conn);
    return ['ok' => true, 'message' => 'Delivery status updated to ' . $rule['to'] . '.'];
}
