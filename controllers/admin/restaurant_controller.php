<?php
/**
 * FoodHub - Procedural Admin Restaurant Controller
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/restaurant_model.php';

$error = "";
$success = "";

// 1. Handle Restaurant Status Update (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
    $new_status    = trim($_POST['status'] ?? '');

    $allowed_statuses = ['Pending', 'Approved', 'Rejected', 'Suspended'];

    if ($restaurant_id <= 0 || empty($new_status)) {
        $error = "Invalid restaurant ID or status specified.";
    } elseif (!in_array($new_status, $allowed_statuses, true)) {
        $error = "Invalid status value provided.";
    } else {
        if (update_restaurant_status($conn, $restaurant_id, $new_status)) {
            $success = "Restaurant #$restaurant_id status was successfully updated to '$new_status'.";
        } else {
            $error = "Failed to update restaurant status: " . mysqli_error($conn);
        }
    }
}

// 2. Fetch Restaurants with Owner Information
$restaurants = get_all_restaurants($conn);
$currentPage = 'restaurants';
