<?php
// manager/models/RestaurantModel.php

function getRestaurantsByManager($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT DISTINCT r.*, COALESCE(rm.role_title, 'owner') AS role_title 
        FROM restaurants r
        LEFT JOIN restaurant_managers rm ON r.restaurant_id = rm.restaurant_id
        WHERE rm.user_id = ? OR r.user_id = ?
        ORDER BY r.restaurant_id DESC
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRestaurantByIdAndManager($conn, $restaurant_id, $user_id) {
    $stmt = $conn->prepare("
        SELECT DISTINCT r.*, COALESCE(rm.role_title, 'owner') AS role_title 
        FROM restaurants r
        LEFT JOIN restaurant_managers rm ON r.restaurant_id = rm.restaurant_id
        WHERE r.restaurant_id = ? AND (rm.user_id = ? OR r.user_id = ?)
        LIMIT 1
    ");
    $stmt->bind_param("iii", $restaurant_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
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
    $stmt = $conn->prepare("
        UPDATE restaurants 
        SET name = ?, description = ?, address = ?, phone = ?, cuisine_type = ?
        WHERE restaurant_id = ?
    ");
    $stmt->bind_param("sssssi", $name, $description, $address, $phone, $cuisine_type, $restaurant_id);
    return $stmt->execute();
}

function toggleRestaurantAvailability($conn, $restaurant_id, $user_id, $is_open) {
    if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
        return false;
    }
    $stmt = $conn->prepare("UPDATE restaurants SET is_open = ? WHERE restaurant_id = ?");
    $stmt->bind_param("ii", $is_open, $restaurant_id);
    return $stmt->execute();
}

function deleteRestaurant($conn, $restaurant_id, $user_id) {
    if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
        return false;
    }
    $stmt = $conn->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
    $stmt->bind_param("i", $restaurant_id);
    return $stmt->execute();
}
