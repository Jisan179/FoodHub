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

        if ($user) {
            // Check account status
            if ($user['status'] === 'Suspended') {
                $error = "Your account has been suspended. Please contact support.";
            } elseif ($user['status'] === 'Inactive') {
                $error = "Your account is currently inactive. Please contact an administrator.";
            } else {
                // Verify password (supports bcrypt hash and plain text seed password fallback)
                $password_matches = (password_verify($password, $user['password']) || $user['password'] === $password);

                if ($password_matches) {
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
                    } else {
                        if (isset($_COOKIE['foodhub_user'])) {
                            setcookie('foodhub_user', '', time() - 3600, "/");
                        }
                    }

                    // Role-Based Redirection
                    $redirect_url = get_user_dashboard_url($_SESSION['role']);
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    $error = "Invalid password. Please check your credentials and try again.";
                }
            }
        } else {
            $error = "No active account found with that username or email address.";
        }
    }
}
