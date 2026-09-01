<?php
// manager/models/FoodModel.php

/**
 * Get all active (non-deleted) food items for a specific restaurant.
 */
function getFoodByRestaurant($conn, $restaurant_id) {
    $stmt = $conn->prepare("
        SELECT * FROM food_items 
        WHERE restaurant_id = ? AND is_deleted = 0
        ORDER BY category, name
    ");
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get a specific food item.
 */
function getFoodById($conn, $item_id, $restaurant_id) {
    $stmt = $conn->prepare("
        SELECT * FROM food_items 
        WHERE item_id = ? AND restaurant_id = ? AND is_deleted = 0
    ");
    $stmt->bind_param("ii", $item_id, $restaurant_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Insert a new food item.
 */
function insertFood($conn, $restaurant_id, $name, $description, $price, $category, $status) {
    $stmt = $conn->prepare("
        INSERT INTO food_items (restaurant_id, name, description, price, category, status, is_deleted) 
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");
    $stmt->bind_param("issdss", $restaurant_id, $name, $description, $price, $category, $status);
    return $stmt->execute();
}

/**
 * Update an existing food item.
 */
function updateFood($conn, $item_id, $restaurant_id, $name, $description, $price, $category, $status) {
    $stmt = $conn->prepare("
        UPDATE food_items 
        SET name = ?, description = ?, price = ?, category = ?, status = ?
        WHERE item_id = ? AND restaurant_id = ? AND is_deleted = 0
    ");
    $stmt->bind_param("ssdssii", $name, $description, $price, $category, $status, $item_id, $restaurant_id);
    return $stmt->execute();
}

/**
 * Soft delete a food item.
 */
function softDeleteFood($conn, $item_id, $restaurant_id) {
    $stmt = $conn->prepare("
        UPDATE food_items 
        SET is_deleted = 1 
        WHERE item_id = ? AND restaurant_id = ?
    ");
    $stmt->bind_param("ii", $item_id, $restaurant_id);
    return $stmt->execute();
}
