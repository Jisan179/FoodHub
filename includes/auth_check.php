<?php
/**
 * FoodHub - Session Validation & Role-Based Access Control (RBAC) Middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user profile details from session
 */
function get_logged_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'user_id'  => $_SESSION['user_id'] ?? null,
        'name'     => $_SESSION['name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => normalize_role($_SESSION['role'] ?? 'Customer'),
        'phone'    => $_SESSION['phone'] ?? '',
        'address'  => $_SESSION['address'] ?? '',
        'status'   => $_SESSION['status'] ?? 'Active',
    ];
}

/**
 * Normalize role names (e.g. 'Admin' -> 'Administrator')
 */
function normalize_role($role) {
    if (strcasecmp($role, 'Admin') === 0 || strcasecmp($role, 'Administrator') === 0) {
        return 'Administrator';
    }
    return $role;
}

/**
 * Resolve correct relative path to login page depending on current directory
 */
function get_relative_login_url() {
    $script = $_SERVER['PHP_SELF'] ?? '';
    if (strpos($script, '/admin/') !== false || strpos($script, '/views/admin/') !== false) {
        return '../login.php';
    }
    if (strpos($script, '/views/') !== false) {
        return '../login.php';
    }
    return 'login.php';
}

/**
 * Resolve correct relative path to base URL / dashboard
 */
function get_user_dashboard_url($role = null) {
    if ($role === null && is_logged_in()) {
        $role = $_SESSION['role'] ?? 'Customer';
    }
    $norm_role = normalize_role($role ?? 'Customer');
    $script = $_SERVER['PHP_SELF'] ?? '';
    $in_admin_dir = (strpos($script, '/admin/') !== false);

    if ($norm_role === 'Administrator') {
        return $in_admin_dir ? 'dashboard.php' : 'admin/dashboard.php';
    } else {
        return $in_admin_dir ? '../dashboard.php' : 'dashboard.php';
    }
}

/**
 * Guard function to enforce authentication and role access
 * @param array|string $allowed_roles List of allowed roles or single role (empty array = any authenticated user)
 */
function check_auth($allowed_roles = []) {
    if (!is_logged_in()) {
        $login_url = get_relative_login_url();
        header("Location: " . $login_url);
        exit();
    }

    if (!empty($allowed_roles)) {
        if (!is_array($allowed_roles)) {
            $allowed_roles = [$allowed_roles];
        }

        $normalized_allowed = array_map('normalize_role', $allowed_roles);
        $current_role = normalize_role($_SESSION['role'] ?? '');

        if (!in_array($current_role, $normalized_allowed, true)) {
            // Role not authorized, redirect to their personalized dashboard
            $target_dashboard = get_user_dashboard_url($current_role);
            $_SESSION['flash_error'] = "Access Denied: You do not have permission to access that area.";
            header("Location: " . $target_dashboard);
            exit();
        }
    }
}
