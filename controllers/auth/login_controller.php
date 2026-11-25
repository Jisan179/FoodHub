<?php
/**
 * FoodHub - Authentication & Role-Based Login Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . get_user_dashboard_url());
    exit();
}

$error = "";
$success = "";
$username = "";

// Check for flash messages
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Check if registration success banner requested
if (isset($_GET['registered']) && empty($success)) {
    $success = "Registration successful! Please sign in with your new credentials.";
}

// Check if logout banner requested
if (isset($_GET['logged_out']) && empty($success)) {
    $success = "You have been safely signed out. See you again soon!";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username    = trim($_POST['username'] ?? '');
    $password    = trim($_POST['password'] ?? '');
    $remember_me = isset($_POST['remember_me']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both your username/email and password.";
    } else {
        $user = find_user_by_username_or_email($conn, $username);

        // Same message for unknown user and wrong password, so accounts cannot be probed
        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid username/email or password.";
        } elseif ($user['status'] === 'Suspended') {
            // Account status is only revealed after the password is proven
            $error = "Your account has been suspended. Please contact support.";
        } elseif ($user['status'] === 'Inactive') {
            $error = "Your account is currently inactive. Please contact an administrator.";
        } else {
            // New session ID on login (prevents session fixation)
            session_regenerate_id(true);

            // Populate session
            $_SESSION['user_id']  = intval($user['user_id']);
            $_SESSION['username'] = $user['username'];
            $_SESSION['name']     = $user['name'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = normalize_role($user['role']);
            $_SESSION['phone']    = $user['phone'] ?? '';
            $_SESSION['address']  = $user['address'] ?? '';
            $_SESSION['status']   = $user['status'];

            // Optional Remember Me cookie
            if ($remember_me) {
                setcookie('foodhub_user', $user['username'], time() + (86400 * 30), "/");
            } elseif (isset($_COOKIE['foodhub_user'])) {
                setcookie('foodhub_user', '', time() - 3600, "/");
            }

            // Role-Based Redirection
            header("Location: " . get_user_dashboard_url($_SESSION['role']));
            exit();
        }
    }
}
