<?php
// manager/models/RestaurantModel.php

function getRestaurantsByManager($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT r.*, rm.role_title 
        FROM restaurants r
        JOIN restaurant_managers rm ON r.restaurant_id = rm.restaurant_id
        WHERE rm.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRestaurantByIdAndManager($conn, $restaurant_id, $user_id) {
    $stmt = $conn->prepare("
        SELECT r.* 
        FROM restaurants r
        JOIN restaurant_managers rm ON r.restaurant_id = rm.restaurant_id
        WHERE r.restaurant_id = ? AND rm.user_id = ?
    ");
    $stmt->bind_param("ii", $restaurant_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function insertRestaurant($conn, $user_id, $name, $description, $address, $phone, $cuisine_type) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO restaurants (user_id, name, description, address, phone, cuisine_type, status, is_open) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', 1)
        ");
        $stmt->bind_param("isssss", $user_id, $name, $description, $address, $phone, $cuisine_type);
        $stmt->execute();
        $restaurant_id = $conn->insert_id;

        $stmt_manager = $conn->prepare("
            INSERT INTO restaurant_managers (user_id, restaurant_id, role_title) 
            VALUES (?, ?, 'owner')
        ");
        $stmt_manager->bind_param("ii", $user_id, $restaurant_id);
        $stmt_manager->execute();

        $conn->commit();
        return $restaurant_id;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
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
