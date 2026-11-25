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
$username_or_email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? 'find_account';

    if ($action === 'find_account') {
        $username_or_email = trim($_POST['username_or_email'] ?? '');
        $phone             = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
        $attempts          = intval($_SESSION['pw_reset_attempts'] ?? 0);
        unset($_SESSION['pw_reset']);

        if ($attempts >= 5) {
            $error = "Too many attempts. Please contact an administrator to reset your password.";
        } elseif (empty($username_or_email) || empty($phone)) {
            $error = "Please enter your username or email and your registered phone number.";
        } else {
            $user = find_user_by_username_or_email($conn, $username_or_email);
            $stored_phone = preg_replace('/\D+/', '', $user['phone'] ?? '');

            // Administrators and suspended accounts can never use self-service reset
            if ($user && $user['status'] !== 'Suspended' && normalize_role($user['role']) !== 'Administrator'
                && $stored_phone !== '' && hash_equals($stored_phone, $phone)) {
                $_SESSION['pw_reset'] = [
                    'user_id'  => intval($user['user_id']),
                    'username' => $user['username'],
                    'expires'  => time() + 600
                ];
                $step = 2;
                $found_username = $user['username'];
            } else {
                $_SESSION['pw_reset_attempts'] = $attempts + 1;
                $error = "Those details could not be verified. Please check them or contact an administrator.";
            }
        }
    } elseif ($action === 'reset_password') {
        // The target account comes ONLY from the verified session, never from the form
        $reset            = $_SESSION['pw_reset'] ?? null;
        $user_id          = ($reset && $reset['expires'] >= time()) ? intval($reset['user_id']) : 0;
        $found_username   = $reset['username'] ?? '';
        $new_password     = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if ($user_id <= 0) {
            $error = "Your verification expired. Please verify your account again.";
            $step = 1;
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = "Please enter and confirm your new password.";
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match. Please re-enter.";
            $step = 2;
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $updated = update_user_password($conn, $user_id, $hashed);

            if ($updated) {
                unset($_SESSION['pw_reset'], $_SESSION['pw_reset_attempts']);
                $_SESSION['flash_success'] = "Your password has been successfully reset! Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Failed to update password. Please try again.";
                $step = 2;
            }
        }
    }
}
