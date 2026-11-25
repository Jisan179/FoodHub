<?php
// manager/models/RestaurantModel.php

function getRestaurantsByManager($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT DISTINCT r.*, COALESCE(rm.role_title, 'owner') AS role_title 
        FROM restaurants r
        LEFT JOIN restaurant_managers rm ON r.restaurant_id = rm.restaurant_id
        WHERE rm.user_id = ? OR r.user_id = ?
        ORDER BY r.restaurant_id DESC
    ");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getRestaurantByIdAndManager($conn, $restaurant_id, $user_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT DISTINCT r.*, COALESCE(rm.role_title, 'owner') AS role_title 
        FROM restaurants r
        LEFT JOIN restaurant_managers rm ON r.restaurant_id = rm.restaurant_id
        WHERE r.restaurant_id = ? AND (rm.user_id = ? OR r.user_id = ?)
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "iii", $restaurant_id, $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function insertRestaurant($conn, $user_id, $name, $description, $address, $phone, $cuisine_type) {
    mysqli_begin_transaction($conn);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO restaurants (user_id, name, description, address, phone, cuisine_type, status, is_open) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending', 1)
    ");
    mysqli_stmt_bind_param($stmt, "isssss", $user_id, $name, $description, $address, $phone, $cuisine_type);
    $inserted = mysqli_stmt_execute($stmt);

    if (!$inserted) {
        mysqli_rollback($conn);
        return false;
    }

    $restaurant_id = mysqli_insert_id($conn);

    $stmt_manager = mysqli_prepare($conn, "
        INSERT INTO restaurant_managers (user_id, restaurant_id, role_title) 
        VALUES (?, ?, 'owner')
    ");
    mysqli_stmt_bind_param($stmt_manager, "ii", $user_id, $restaurant_id);
    $linked = mysqli_stmt_execute($stmt_manager);

    if (!$linked) {
        mysqli_rollback($conn);
        return false;
    }

    mysqli_commit($conn);
    return $restaurant_id;
}


function updateRestaurant($conn, $restaurant_id, $user_id, $name, $description, $address, $phone, $cuisine_type) {
    if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
        return false;
    }
    $stmt = mysqli_prepare($conn, "
        UPDATE restaurants 
        SET name = ?, description = ?, address = ?, phone = ?, cuisine_type = ?
        WHERE restaurant_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "sssssi", $name, $description, $address, $phone, $cuisine_type, $restaurant_id);
    return mysqli_stmt_execute($stmt);
}

function toggleRestaurantAvailability($conn, $restaurant_id, $user_id, $is_open) {
    if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
        return false;
    }
    $stmt = mysqli_prepare($conn, "UPDATE restaurants SET is_open = ? WHERE restaurant_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $is_open, $restaurant_id);
    return mysqli_stmt_execute($stmt);
}

function deleteRestaurant($conn, $restaurant_id, $user_id) {
    if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
        return false;
    }
    // Deleting would cascade into customers' orders, deliveries and reviews
    $check = mysqli_query($conn, "SELECT 1 FROM orders WHERE restaurant_id = " . intval($restaurant_id) . " LIMIT 1");
    if (!$check || mysqli_num_rows($check) > 0) {
        return false;
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM restaurants WHERE restaurant_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
    return mysqli_stmt_execute($stmt);
}
