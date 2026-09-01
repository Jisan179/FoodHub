<?php
/**
 * FoodHub - Change Password Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Enforce login
check_auth();

$user_id = intval($_SESSION['user_id']);
$user = find_user_by_id($conn, $user_id);

if (!$user) {
    header("Location: logout.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all password fields.";
    } elseif (strlen($new_password) < 6) {
        $error = "Your new password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match. Please re-enter.";
    } else {
        // Verify current password (supports bcrypt hash and plain text fallback)
        $matches = (password_verify($current_password, $user['password']) || $user['password'] === $current_password);

        if (!$matches) {
            $error = "Current password is incorrect. Please check and try again.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $updated = update_user_password($conn, $user_id, $hashed);

            if ($updated) {
                $success = "Password changed successfully! Keep it secure.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
}
