<?php
/**
 * FoodHub - Restaurant Model
 * Procedural MySQLi Helpers with Prepared Statements
 */

/**
 * Get all partner restaurants with owner info and item count
 */
function get_all_restaurants($conn) {
    $sql = "
        SELECT 
            r.restaurant_id,
            r.name AS restaurant_name,
            r.description,
            r.address,
            r.phone,
            r.status AS restaurant_status,
            r.created_at,
            u.user_id AS owner_id,
            u.name AS owner_name,
            u.email AS owner_email,
            u.username AS owner_username,
            (SELECT COUNT(*) FROM food_items f WHERE f.restaurant_id = r.restaurant_id) AS total_items
        FROM restaurants r
        JOIN users u ON r.user_id = u.user_id
        ORDER BY r.restaurant_id DESC
    ";

    $result = mysqli_query($conn, $sql);
    $restaurants = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $restaurants[] = $row;
        }
    }

    return $restaurants;
}

/**
 * Get all approved restaurants (for customer view / dashboard)
 */
function get_approved_restaurants($conn, $limit = 10) {
    $limit = intval($limit);
    $sql = "
        SELECT 
            r.restaurant_id,
            r.name AS restaurant_name,
            r.description,
            r.address,
            r.phone,
            r.created_at,
            (SELECT COUNT(*) FROM food_items f WHERE f.restaurant_id = r.restaurant_id AND f.status = 'Available') AS available_items
        FROM restaurants r
        WHERE r.status = 'Approved'
        ORDER BY r.restaurant_id DESC
        LIMIT $limit
    ";

    $result = mysqli_query($conn, $sql);
    $restaurants = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $restaurants[] = $row;
        }
    }

    return $restaurants;
}

/**
 * Get restaurant managed by a specific user (Manager)
 */
function get_restaurant_by_manager_id($conn, $user_id) {
    $user_id = intval($user_id);
    $stmt = mysqli_prepare($conn, "SELECT * FROM restaurants WHERE user_id = ? LIMIT 1");
    if (!$stmt) return null;

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $restaurant = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $restaurant;
}

/**
 * Get food items for a restaurant
 */
function get_food_items_by_restaurant($conn, $restaurant_id) {
    $restaurant_id = intval($restaurant_id);
    $stmt = mysqli_prepare($conn, "SELECT * FROM food_items WHERE restaurant_id = ? ORDER BY category ASC, name ASC");
    if (!$stmt) return [];

    mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $items = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $items;
}

/**
 * Get pending restaurants awaiting approval
 */
function get_pending_restaurants($conn, $limit = 5) {
    $safe_limit = intval($limit);
    $sql = "
        SELECT 
            r.restaurant_id,
            r.name AS restaurant_name,
            u.name AS owner_name,
            r.phone,
            r.address,
            r.created_at
        FROM restaurants r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.status = 'Pending'
        ORDER BY r.restaurant_id DESC
        LIMIT $safe_limit
    ";

    $result = mysqli_query($conn, $sql);
    $pending = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pending[] = $row;
        }
    }

    return $pending;
}

/**
 * Count total pending restaurant applications
 */
function count_pending_restaurants($conn) {
    $sql = "SELECT COUNT(*) AS total FROM restaurants WHERE status = 'Pending'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['total'] ?? 0);
    }
    return 0;
}

/**
 * Update restaurant status
 */
function update_restaurant_status($conn, $restaurant_id, $status) {
    $allowed_statuses = ['Pending', 'Approved', 'Rejected', 'Suspended'];
    if (!in_array($status, $allowed_statuses, true)) {
        return false;
    }

    $id = intval($restaurant_id);
    $stmt = mysqli_prepare($conn, "UPDATE restaurants SET status = ? WHERE restaurant_id = ?");
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}
