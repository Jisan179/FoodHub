<?php
/**
 * FoodHub - Procedural Restaurant Model
 * Pure procedural functions for restaurants table
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

    $safe_id = intval($restaurant_id);
    $safe_status = mysqli_real_escape_string($conn, trim($status));

    $sql = "UPDATE restaurants SET status = '$safe_status' WHERE restaurant_id = $safe_id";
    return (bool)mysqli_query($conn, $sql);
}
