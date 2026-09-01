<?php
/**
 * FoodHub - User Model
 * Prepared Statements & Procedural MySQLi Helpers
 */

/**
 * Find user by username or email
 */
function find_user_by_username_or_email($conn, $identifier) {
    $identifier = trim($identifier);
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
    if (!$stmt) return null;

    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $user;
}

/**
 * Find an active user by username
 */
function find_user_by_username($conn, $username) {
    $username = trim($username);
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? LIMIT 1");
    if (!$stmt) return null;

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $user;
}

/**
 * Find a user by their user_id
 */
function find_user_by_id($conn, $id) {
    $id = intval($id);
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ? LIMIT 1");
    if (!$stmt) return null;

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $user;
}

/**
 * Check if a username or email is already taken, optionally excluding a user ID (for edit profile)
 */
function check_user_exists($conn, $username, $email, $exclude_id = 0) {
    $username = trim($username);
    $email = trim($email);
    $exclude_id = intval($exclude_id);

    if ($exclude_id > 0) {
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ? LIMIT 1");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $exclude_id);
    } else {
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = ($result && mysqli_num_rows($result) > 0);
    mysqli_stmt_close($stmt);
    return $exists;
}

/**
 * Create a new user account with secure password hashing
 */
function create_user($conn, $data) {
    $name     = trim($data['name'] ?? $data['fullname'] ?? '');
    $username = trim($data['username'] ?? '');
    $email    = trim($data['email'] ?? '');
    $raw_pass = trim($data['password'] ?? '');
    $role     = trim($data['role'] ?? 'Customer');
    $address  = trim($data['address'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $status   = trim($data['status'] ?? 'Active');

    // Hash password if not already hashed
    if (strpos($raw_pass, '$2y$') === 0 || strpos($raw_pass, '$argon2') === 0) {
        $hashed_pass = $raw_pass;
    } else {
        $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);
    }

    $stmt = mysqli_prepare($conn, "
        INSERT INTO users (name, username, email, password, role, address, phone, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "ssssssss", $name, $username, $email, $hashed_pass, $role, $address, $phone, $status);
    $success = mysqli_stmt_execute($stmt);
    $new_id = $success ? mysqli_insert_id($conn) : false;
    mysqli_stmt_close($stmt);
    return $new_id ? $new_id : $success;
}

/**
 * Update user details (Profile or Admin edit)
 */
function update_user($conn, $id, $data) {
    $id       = intval($id);
    $name     = trim($data['name'] ?? $data['fullname'] ?? '');
    $email    = trim($data['email'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $address  = trim($data['address'] ?? '');
    $role     = isset($data['role']) ? trim($data['role']) : null;
    $status   = isset($data['status']) ? trim($data['status']) : null;

    if ($role !== null && $status !== null) {
        $stmt = mysqli_prepare($conn, "
            UPDATE users 
            SET name = ?, email = ?, phone = ?, address = ?, role = ?, status = ? 
            WHERE user_id = ?
        ");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "ssssssi", $name, $email, $phone, $address, $role, $status, $id);
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE users 
            SET name = ?, email = ?, phone = ?, address = ? 
            WHERE user_id = ?
        ");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $address, $id);
    }

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/**
 * Update a user's password with hashed value
 */
function update_user_password($conn, $id, $new_hashed_password) {
    $id = intval($id);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "si", $new_hashed_password, $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/**
 * Update user operational status
 */
function update_user_status($conn, $id, $status) {
    $id = intval($id);
    $allowed = ['Active', 'Inactive', 'Suspended'];
    if (!in_array($status, $allowed, true)) return false;

    $stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE user_id = ?");
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/**
 * Delete a user by ID
 */
function delete_user($conn, $user_id) {
    $id = intval($user_id);
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, "i", $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/**
 * Filtered user query with live search, role filter, status filter, sorting, and pagination
 */
function get_filtered_users($conn, $search = null, $role = null, $status = null, $sort_by = 'user_id', $sort_order = 'DESC', $limit = 10, $offset = 0) {
    $where_clauses = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($search)) {
        $search_term = "%" . trim($search) . "%";
        $where_clauses[] = "(name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ? OR address LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "sssss";
    }

    if (!empty($role) && $role !== 'All') {
        $where_clauses[] = "role = ?";
        $params[] = trim($role);
        $types .= "s";
    }

    if (!empty($status) && $status !== 'All') {
        $where_clauses[] = "status = ?";
        $params[] = trim($status);
        $types .= "s";
    }

    $where_sql = implode(" AND ", $where_clauses);

    // Validate sorting
    $allowed_sort = ['user_id', 'name', 'username', 'email', 'role', 'status', 'created_at'];
    $sort_column = in_array($sort_by, $allowed_sort, true) ? $sort_by : 'user_id';
    $sort_dir = (strtoupper($sort_order) === 'ASC') ? 'ASC' : 'DESC';

    $limit = intval($limit);
    $offset = intval($offset);

    $sql = "SELECT * FROM users WHERE {$where_sql} ORDER BY {$sort_column} {$sort_dir} LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return [];

    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $users = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $users;
}

/**
 * Count total filtered users for pagination
 */
function count_filtered_users($conn, $search = null, $role = null, $status = null) {
    $where_clauses = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($search)) {
        $search_term = "%" . trim($search) . "%";
        $where_clauses[] = "(name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ? OR address LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "sssss";
    }

    if (!empty($role) && $role !== 'All') {
        $where_clauses[] = "role = ?";
        $params[] = trim($role);
        $types .= "s";
    }

    if (!empty($status) && $status !== 'All') {
        $where_clauses[] = "status = ?";
        $params[] = trim($status);
        $types .= "s";
    }

    $where_sql = implode(" AND ", $where_clauses);
    $sql = "SELECT COUNT(*) AS total FROM users WHERE {$where_sql}";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return 0;

    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total = intval($row['total'] ?? 0);
    }
    mysqli_stmt_close($stmt);
    return $total;
}

/**
 * Breakdown of user counts by role
 */
function count_users_by_role($conn) {
    $sql = "SELECT role, COUNT(*) AS count FROM users GROUP BY role";
    $result = mysqli_query($conn, $sql);
    $counts = [
        'Administrator'      => 0,
        'Customer'           => 0,
        'Restaurant Manager' => 0,
        'Rider'              => 0,
        'total'              => 0
    ];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $role_name = $row['role'];
            $cnt = intval($row['count']);
            $counts[$role_name] = $cnt;
            $counts['total'] += $cnt;
        }
    }
    return $counts;
}

/**
 * Count total registered users
 */
function count_total_users($conn) {
    $sql = "SELECT COUNT(*) AS total FROM users";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['total'] ?? 0);
    }
    return 0;
}

/**
 * Get list of active riders for assignment dropdown
 */
function get_active_riders($conn) {
    $sql = "SELECT user_id, name, phone FROM users WHERE role = 'Rider' AND status = 'Active' ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    $riders = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $riders[] = $row;
        }
    }
    return $riders;
}

/**
 * Legacy support for simple all-users query
 */
function get_all_users($conn, $search = null) {
    return get_filtered_users($conn, $search, null, null, 'user_id', 'DESC', 100, 0);
}
