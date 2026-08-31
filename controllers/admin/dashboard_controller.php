<?php
/**
 * FoodHub - Procedural Admin Dashboard Controller
 */

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/user_model.php';
require_once __DIR__ . '/../../models/restaurant_model.php';
require_once __DIR__ . '/../../models/order_model.php';

$total_users         = count_total_users($conn);
$pending_approvals   = count_pending_restaurants($conn);
$total_orders        = count_total_orders($conn);
$total_revenue       = get_total_revenue($conn);
$pending_restaurants = get_pending_restaurants($conn, 5);
$recent_orders       = get_recent_orders($conn, 5);
$currentPage         = 'dashboard';
