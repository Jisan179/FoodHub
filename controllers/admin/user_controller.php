<?php
/**
 * FoodHub - Administrator User Management Controller
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Enforce Administrator role
check_auth(['Administrator', 'Admin']);

$error = "";
$success = "";

if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// ----------------------------------------------------
// 1. CREATE USER (POST)
// ----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    $name     = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? 'Customer');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $status   = trim($_POST['status'] ?? 'Active');

    $allowed_roles = ['Administrator', 'Customer', 'Restaurant Manager', 'Rider', 'Admin'];
    $allowed_statuses = ['Active', 'Inactive', 'Suspended'];

    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields (Name, Username, Email, Password).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = "Username must be 3-30 characters with alphanumeric and underscore characters only.";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $error = "Invalid user role selected.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (check_user_exists($conn, $username, $email)) {
        $error = "A user with that username or email address already exists.";
    } else {
        $created_id = create_user($conn, [
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => normalize_role($role),
            'phone'    => $phone,
            'address'  => $address,
            'status'   => in_array($status, $allowed_statuses, true) ? $status : 'Active'
        ]);

        if ($created_id) {
            $_SESSION['flash_success'] = "User account @{$username} ({$name}) created successfully!";
            header("Location: users.php");
            exit();
        } else {
            $error = "Failed to create user account. Please check database connectivity.";
        }
    }
}

// ----------------------------------------------------
// 2. EDIT USER (POST)
// ----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $edit_id      = intval($_POST['user_id'] ?? 0);
    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $role         = trim($_POST['role'] ?? 'Customer');
    $status       = trim($_POST['status'] ?? 'Active');
    $new_password = trim($_POST['new_password'] ?? '');

    $target_user = find_user_by_id($conn, $edit_id);

    if (!$target_user) {
        $error = "Target user account not found.";
    } elseif (empty($name) || empty($email)) {
        $error = "Name and Email are required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (check_user_exists($conn, $target_user['username'], $email, $edit_id)) {
        $error = "Email address is already in use by another user.";
    } else {
        $updated = update_user($conn, $edit_id, [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
            'role'    => normalize_role($role),
            'status'  => $status
        ]);

        // If a new password was supplied, update it
        if ($updated && !empty($new_password)) {
            if (strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                update_user_password($conn, $edit_id, $hashed);
            } else {
                $_SESSION['flash_error'] = "User updated, but password was not changed (must be at least 6 characters).";
                header("Location: users.php");
                exit();
            }
        }

        if ($updated) {
            $_SESSION['flash_success'] = "User account @{$target_user['username']} updated successfully!";
            header("Location: users.php");
            exit();
        } else {
            $error = "Failed to update user. Please try again.";
        }
    }
}

// ----------------------------------------------------
// 3. DELETE USER (GET / POST)
// ----------------------------------------------------
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $current_admin_id = intval($_SESSION['user_id'] ?? 0);

    if ($delete_id === $current_admin_id) {
        $error = "Security Restriction: You cannot delete your own logged-in Administrator account.";
    } else {
        $target = find_user_by_id($conn, $delete_id);
        if ($target) {
            if (delete_user($conn, $delete_id)) {
                $_SESSION['flash_success'] = "User #{$delete_id} (@{$target['username']}) deleted successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to delete user.";
            }
        } else {
            $_SESSION['flash_error'] = "User not found.";
        }
        header("Location: users.php");
        exit();
    }
}

// ----------------------------------------------------
// 4. QUERY PARAMETERS FOR SEARCH, FILTER, SORT & PAGINATION
// ----------------------------------------------------
$search_query = trim($_GET['search'] ?? '');
$role_filter  = trim($_GET['role'] ?? 'All');
$status_filter = trim($_GET['status'] ?? 'All');
$sort_by      = trim($_GET['sort_by'] ?? 'user_id');
$sort_order   = trim($_GET['sort_order'] ?? 'DESC');
$page         = max(1, intval($_GET['page'] ?? 1));
$limit        = 10;
$offset       = ($page - 1) * $limit;

// Fetch Role Counts Breakdown
$role_counts = count_users_by_role($conn);

// Total filtered users & pages
$total_filtered = count_filtered_users($conn, $search_query, $role_filter, $status_filter);
$total_pages = max(1, ceil($total_filtered / $limit));

// Fetch paginated user records
$users = get_filtered_users($conn, $search_query, $role_filter, $status_filter, $sort_by, $sort_order, $limit, $offset);
