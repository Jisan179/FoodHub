<?php
/**
 * FoodHub - Procedural Admin User Controller
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';

$error = "";
$success = "";

// 1. Handle User Deletion (GET request ?delete_id=X)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $current_admin_id = intval($_SESSION['user_id'] ?? 0);

    if ($delete_id <= 0) {
        $error = "Invalid user ID specified for deletion.";
    } elseif ($delete_id === $current_admin_id) {
        $error = "Security Warning: You cannot delete your own active administrator account.";
    } else {
        if (delete_user($conn, $delete_id)) {
            $success = "User (ID: #$delete_id) was successfully deleted.";
        } else {
            $error = "Failed to delete user: " . mysqli_error($conn);
        }
    }
}

// 2. Handle User Creation (POST request)
$name     = "";
$username = "";
$email    = "";
$role     = "Customer";
$address  = "";
$phone    = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    $name     = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? 'Customer');
    $address  = trim($_POST['address'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields (Name, Username, Email, Password, and Role).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (check_user_exists($conn, $username, $email)) {
        $error = "A user with this username or email already exists.";
    } else {
        $created = create_user($conn, [
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => $role,
            'address'  => $address,
            'phone'    => $phone,
            'status'   => 'Active'
        ]);

        if ($created) {
            $success = "New user '$username' ($role) created successfully!";
            // Reset form fields after successful insert
            $name     = "";
            $username = "";
            $email    = "";
            $role     = "Customer";
            $address  = "";
            $phone    = "";
        } else {
            $error = "Database error creating user: " . mysqli_error($conn);
        }
    }
}

// 3. Fetch Users List with Optional Search Filter
$search_query = trim($_GET['search'] ?? '');
$users = get_all_users($conn, !empty($search_query) ? $search_query : null);
$currentPage = 'users';
