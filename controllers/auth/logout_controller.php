<?php
/**
 * FoodHub - User Logout Controller
 * Securely ends user session, clears cookies, and redirects to login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session values
$_SESSION = [];

// Clear remember me cookie
if (isset($_COOKIE['foodhub_user'])) {
    setcookie('foodhub_user', '', time() - 3600, '/');
}

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Compute relative path to root login.php
$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
if (strpos($script_path, '/actions/') !== false || strpos($script_path, '/manager/views/') !== false || strpos($script_path, '/manager/controllers/') !== false) {
    $redirect_url = '../../login.php?logged_out=1';
} elseif (strpos($script_path, '/admin/') !== false || strpos($script_path, '/customer/') !== false || strpos($script_path, '/rider/') !== false || strpos($script_path, '/controllers/') !== false || strpos($script_path, '/views/') !== false) {
    $redirect_url = '../login.php?logged_out=1';
} else {
    $redirect_url = 'login.php?logged_out=1';
}

header("Location: " . $redirect_url);
exit();
