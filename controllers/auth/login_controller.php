<?php
/**
 * FoodHub - Procedural Login Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in as Admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header("Location: admin/dashboard.php");
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
                if ($user['role'] === 'Admin') {
                    $_SESSION['user_id']  = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'];
                    $_SESSION['name']     = $user['name'];

                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $error = "Access denied. Only Admin accounts can access this portal (Current role: " . htmlspecialchars($user['role']) . ").";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
