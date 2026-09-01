<?php
/**
 * FoodHub - Customer Favorites Controller
 */

require_once __DIR__ . '/../auth/customer_auth_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/customer_model.php';

$customer_id = intval($_SESSION['user_id']);
$favorites   = get_customer_favorites($conn, $customer_id);

$currentPage = 'favorites';
