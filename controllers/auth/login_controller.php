<?php
/**
 * FoodHub - Procedural Login Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in.
if (isset($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'Rider' ? 'rider/dashboard.php' : 'admin/dashboard.php'));
    exit();
}

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        require_once __DIR__ . '/../../config/db.php';
        require_once __DIR__ . '/../../models/user_model.php';

        $user = find_user_by_username($conn, $username);

        if ($user) {
            // Validate password (supports both plain text seed passwords and password_verify hashes)
            $password_matches = ($user['password'] === $password || password_verify($password, $user['password']));

            if ($password_matches) {
                if (in_array($user['role'], ['Admin', 'Rider'], true) && $user['status'] === 'Active') {
                    $_SESSION['user_id']  = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'];
                    $_SESSION['name']     = $user['name'];

                    header("Location: " . ($user['role'] === 'Rider' ? 'rider/dashboard.php' : 'admin/dashboard.php'));
                    exit();
                } else {
                    $error = "Access denied. This portal is available to Admin and Rider accounts.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
