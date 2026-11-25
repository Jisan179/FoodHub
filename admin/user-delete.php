<?php
/**
 * FoodHub - Dedicated Admin User Deletion Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Enforce Administrator role
check_auth(['Administrator', 'Admin']);

require_post();

$delete_id = intval($_POST['id'] ?? 0);
$current_admin_id = intval($_SESSION['user_id'] ?? 0);

if ($delete_id <= 0) {
    $_SESSION['flash_error'] = "Invalid user ID specified for deletion.";
} elseif ($delete_id === $current_admin_id) {
    $_SESSION['flash_error'] = "Security Restriction: You cannot delete your own logged-in Administrator account.";
} else {
    $target = find_user_by_id($conn, $delete_id);
    if ($target) {
        if (delete_user($conn, $delete_id)) {
            $_SESSION['flash_success'] = "User account @{$target['username']} was successfully removed.";
        } else {
            $_SESSION['flash_error'] = user_has_order_history($conn, $delete_id)
                ? "This account has order history and cannot be deleted. Set its status to Inactive instead."
                : "Failed to remove user account from database.";
        }
    } else {
        $_SESSION['flash_error'] = "User account not found.";
    }
}

header("Location: users.php");
exit();
