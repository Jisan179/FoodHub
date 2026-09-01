<?php
/**
 * FoodHub - User Profile Management Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Ensure user is authenticated
check_auth();

$user_id = intval($_SESSION['user_id']);
$user = find_user_by_id($conn, $user_id);

if (!$user) {
    header("Location: logout.php");
    exit();
}

$error = "";
$success = "";

// Handle account deactivation / self-deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'deactivate_account') {
    $confirm_text = trim($_POST['confirm_deactivate'] ?? '');
    if ($confirm_text !== 'DELETE' && $confirm_text !== 'DEACTIVATE') {
        $error = "Please type 'DELETE' or 'DEACTIVATE' to confirm account closure.";
    } else {
        if ($confirm_text === 'DELETE') {
            // Check if current user is the sole administrator
            if (normalize_role($user['role']) === 'Administrator') {
                $user_counts = count_users_by_role($conn);
                if (($user_counts['Administrator'] ?? 0) <= 1) {
                    $error = "Cannot delete the sole Administrator account.";
                } else {
                    delete_user($conn, $user_id);
                    header("Location: logout.php");
                    exit();
                }
            } else {
                delete_user($conn, $user_id);
                header("Location: logout.php");
                exit();
            }
        } else {
            update_user_status($conn, $user_id, 'Inactive');
            header("Location: logout.php");
            exit();
        }
    }
}

// Handle profile info update
if ($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_POST['action']) || $_POST['action'] === 'update_profile')) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email)) {
        $error = "Full Name and Email Address are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (check_user_exists($conn, $user['username'], $email, $user_id)) {
        $error = "That email address is already in use by another account.";
    } else {
        $updated = update_user($conn, $user_id, [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address
        ]);

        if ($updated) {
            $_SESSION['name']    = $name;
            $_SESSION['email']   = $email;
            $_SESSION['phone']   = $phone;
            $_SESSION['address'] = $address;

            $success = "Profile updated successfully!";
            $user = find_user_by_id($conn, $user_id); // Refresh data
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}
