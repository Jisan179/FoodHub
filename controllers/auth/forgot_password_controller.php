<?php
/**
 * FoodHub - Forgot & Reset Password Controller
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
$step = 1; // 1 = enter username/email, 2 = reset password
$found_user_id = null;
$username_or_email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? 'find_account';

    if ($action === 'find_account') {
        $username_or_email = trim($_POST['username_or_email'] ?? '');

        if (empty($username_or_email)) {
            $error = "Please enter your registered username or email address.";
        } else {
            $user = find_user_by_username_or_email($conn, $username_or_email);

            if ($user) {
                if ($user['status'] === 'Suspended') {
                    $error = "This account has been suspended. Please contact support.";
                } else {
                    $step = 2;
                    $found_user_id = $user['user_id'];
                    $found_username = $user['username'];
                }
            } else {
                $error = "No account found with that username or email address.";
            }
        }
    } elseif ($action === 'reset_password') {
        $user_id          = intval($_POST['user_id'] ?? 0);
        $new_password     = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if ($user_id <= 0) {
            $error = "Invalid session. Please search for your account again.";
            $step = 1;
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = "Please enter and confirm your new password.";
            $step = 2;
            $found_user_id = $user_id;
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
            $step = 2;
            $found_user_id = $user_id;
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match. Please re-enter.";
            $step = 2;
            $found_user_id = $user_id;
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $updated = update_user_password($conn, $user_id, $hashed);

            if ($updated) {
                $_SESSION['flash_success'] = "Your password has been successfully reset! Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Failed to update password. Please try again.";
                $step = 2;
                $found_user_id = $user_id;
            }
        }
    }
}
