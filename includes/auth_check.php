<?php
/**
 * FoodHub - Session Validation & Role-Based Access Control (RBAC) Middleware
 * Pure Procedural RBAC with Dynamic Relative Path Resolution
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/**
 * CSRF protection: every POST request must carry the session's secret token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf_request() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    $sent = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Your form session has expired. Please go back, refresh the page and try again.');
    }
}

verify_csrf_request();

/**
 * Reject non-POST access to endpoints that change data
 */
function require_post() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header("Location: " . get_user_dashboard_url());
        exit();
    }
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Calculate the exact relative path prefix from the currently executing script to the FoodHub root
 */
function get_foodhub_root_path() {
    $project_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $script_file = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if (empty($script_file)) {
        return '';
    }
    $script_dir = str_replace('\\', '/', realpath(dirname($script_file)));
    if (!$script_dir || $script_dir === $project_root) {
        return '';
    }
    if (strpos($script_dir, $project_root) === 0) {
        $relative = trim(substr($script_dir, strlen($project_root)), '/');
        $parts = explode('/', $relative);
        $depth = count(array_filter($parts, 'strlen'));
        return $depth > 0 ? str_repeat('../', $depth) : '';
    }
    return '';
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
    return get_foodhub_root_path() . 'login.php';
}

/**
 * Resolve correct relative path to base URL / dashboard for each role
 */
function get_user_dashboard_url($role = null) {
    if ($role === null && is_logged_in()) {
        $role = $_SESSION['role'] ?? 'Customer';
    }
    $norm_role = normalize_role($role ?? 'Customer');
    $root = get_foodhub_root_path();

    if ($norm_role === 'Administrator') {
        return $root . 'admin/dashboard.php';
    } elseif ($norm_role === 'Restaurant Manager') {
        return $root . 'manager/views/dashboard.php';
    } elseif ($norm_role === 'Rider') {
        return $root . 'rider/dashboard.php';
    } else {
        return $root . 'customer/dashboard.php';
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

    // Re-read role and status so suspended or deleted accounts lose access immediately
    global $conn;
    if ($conn) {
        $uid = intval($_SESSION['user_id']);
        $res = mysqli_query($conn, "SELECT role, status FROM users WHERE user_id = $uid LIMIT 1");
        $fresh = $res ? mysqli_fetch_assoc($res) : null;
        if (!$fresh || $fresh['status'] !== 'Active') {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['flash_error'] = "Your account is no longer active. Please contact an administrator.";
            header("Location: " . get_relative_login_url());
            exit();
        }
        $_SESSION['role'] = normalize_role($fresh['role']);
    }

    if (!empty($allowed_roles)) {
        if (!is_array($allowed_roles)) {
            $allowed_roles = [$allowed_roles];
        }

        $normalized_allowed = array_map('normalize_role', $allowed_roles);
        $current_role = normalize_role($_SESSION['role'] ?? '');

        if (!in_array($current_role, $normalized_allowed, true)) {
            $target_dashboard = get_user_dashboard_url($current_role);
            $_SESSION['flash_error'] = "Access Denied: You do not have permission to access that area.";
            header("Location: " . $target_dashboard);
            exit();
        }
    }
}
