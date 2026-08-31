<?php
/**
 * FoodHub - Procedural User Model
 * Pure procedural functions for users table
 */

/**
 * Find an active user by username
 */
function find_user_by_username($conn, $username) {
    $safe_username = mysqli_real_escape_string($conn, trim($username));
    $sql = "SELECT * FROM users WHERE username = '$safe_username' AND status = 'Active' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Find a user by their user_id
 */
function find_user_by_id($conn, $id) {
    $safe_id = intval($id);
    $sql = "SELECT * FROM users WHERE user_id = $safe_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Check if a username or email is already taken
 */
function check_user_exists($conn, $username, $email) {
    $safe_username = mysqli_real_escape_string($conn, trim($username));
    $safe_email = mysqli_real_escape_string($conn, trim($email));

    $sql = "SELECT user_id FROM users WHERE username = '$safe_username' OR email = '$safe_email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    return ($result && mysqli_num_rows($result) > 0);
}

/**
 * Create a new user account
 */
function create_user($conn, $data) {
    $name     = mysqli_real_escape_string($conn, trim($data['name'] ?? ''));
    $username = mysqli_real_escape_string($conn, trim($data['username'] ?? ''));
    $email    = mysqli_real_escape_string($conn, trim($data['email'] ?? ''));
    $password = mysqli_real_escape_string($conn, trim($data['password'] ?? ''));
    $role     = mysqli_real_escape_string($conn, trim($data['role'] ?? 'Customer'));
    $address  = mysqli_real_escape_string($conn, trim($data['address'] ?? ''));
    $phone    = mysqli_real_escape_string($conn, trim($data['phone'] ?? ''));
    $status   = mysqli_real_escape_string($conn, trim($data['status'] ?? 'Active'));

    $sql = "
        INSERT INTO users (name, username, email, password, role, address, phone, status) 
        VALUES ('$name', '$username', '$email', '$password', '$role', '$address', '$phone', '$status')
    ";

    return (bool)mysqli_query($conn, $sql);
}

/**
 * Get all users with optional keyword search across multiple fields
 */
function get_all_users($conn, $search = null) {
    if (!empty($search)) {
        $safe_search = mysqli_real_escape_string($conn, trim($search));
        $sql = "
            SELECT * FROM users 
            WHERE name LIKE '%$safe_search%' 
               OR username LIKE '%$safe_search%' 
               OR email LIKE '%$safe_search%' 
               OR role LIKE '%$safe_search%'
               OR phone LIKE '%$safe_search%'
            ORDER BY user_id DESC
        ";
    } else {
        $sql = "SELECT * FROM users ORDER BY user_id DESC";
    }

    $result = mysqli_query($conn, $sql);
    $users = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }

    return $users;
}

/**
 * Delete a user by ID
 */
function delete_user($conn, $user_id) {
    $safe_id = intval($user_id);
    $sql = "DELETE FROM users WHERE user_id = $safe_id";
    return (bool)mysqli_query($conn, $sql);
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
