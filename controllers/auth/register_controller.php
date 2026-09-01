<?php
/**
 * FoodHub - User Registration Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: " . get_user_dashboard_url());
    exit();
}

$error = "";
$success = "";

$name = "";
$username = "";
$email = "";
$phone = "";
$address = "";
$role = "Customer";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name             = trim($_POST['name'] ?? '');
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $role             = trim($_POST['role'] ?? 'Customer');
    $password         = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Allowed public roles (Administrators CANNOT be registered publicly)
    $allowed_roles = ['Customer', 'Restaurant Manager', 'Rider'];

    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields (Full Name, Username, Email, and Password).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = "Username must be 3-30 characters and contain only letters, numbers, and underscores.";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $error = "Invalid role selected. Administrator accounts cannot be registered publicly.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please re-enter.";
    } elseif (check_user_exists($conn, $username, $email)) {
        $error = "An account with that username or email address already exists.";
    } else {
        $user_data = [
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => $role,
            'phone'    => $phone,
            'address'  => $address,
            'status'   => 'Active'
        ];

        $new_user_id = create_user($conn, $user_data);

        if ($new_user_id) {
            // Auto log-in or set flash message
            $_SESSION['flash_success'] = "Registration successful! You can now log in with your credentials.";
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = "Failed to create account. Please try again later.";
        }
    }
}
