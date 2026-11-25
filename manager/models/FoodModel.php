<?php
// manager/models/FoodModel.php

function getFoodByRestaurant($conn, $restaurant_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT * FROM food_items 
        WHERE restaurant_id = ? AND is_deleted = 0
        ORDER BY category, name
    ");
    mysqli_stmt_bind_param($stmt, "i", $restaurant_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

function getFoodById($conn, $item_id, $restaurant_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT * FROM food_items 
        WHERE item_id = ? AND restaurant_id = ? AND is_deleted = 0
    ");
    mysqli_stmt_bind_param($stmt, "ii", $item_id, $restaurant_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function insertFood($conn, $restaurant_id, $name, $description, $price, $category, $status) {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO food_items (restaurant_id, name, description, price, category, status, is_deleted) 
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");
    mysqli_stmt_bind_param($stmt, "issdss", $restaurant_id, $name, $description, $price, $category, $status);
    return mysqli_stmt_execute($stmt);
}

function updateFood($conn, $item_id, $restaurant_id, $name, $description, $price, $category, $status) {
    $stmt = mysqli_prepare($conn, "
        UPDATE food_items 
        SET name = ?, description = ?, price = ?, category = ?, status = ?
        WHERE item_id = ? AND restaurant_id = ? AND is_deleted = 0
    ");
    mysqli_stmt_bind_param($stmt, "ssdssii", $name, $description, $price, $category, $status, $item_id, $restaurant_id);
    return mysqli_stmt_execute($stmt);
}

function softDeleteFood($conn, $item_id, $restaurant_id) {
    $stmt = mysqli_prepare($conn, "
        UPDATE food_items 
        SET is_deleted = 1 
        WHERE item_id = ? AND restaurant_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "ii", $item_id, $restaurant_id);
    return mysqli_stmt_execute($stmt);
}
