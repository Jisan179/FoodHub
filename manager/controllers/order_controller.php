<?php
// manager/controllers/order_controller.php
session_start();
header('Content-Type: application/json');

require_once '../../config/db.php';
require_once '../models/OrderModel.php';
require_once '../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Get all restaurants for this manager
$restaurants = getRestaurantsByManager($conn, $user_id);
$restaurant_ids = array_column($restaurants, 'restaurant_id');

if (empty($restaurant_ids)) {
    echo json_encode(['success' => false, 'message' => 'No restaurants found for this manager.']);
    exit();
}

switch ($action) {
    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_id = intval($_POST['order_id'] ?? 0);
            $new_status = htmlspecialchars($_POST['status'] ?? '');
            
            if (!$order_id || !$new_status) {
                echo json_encode(['success' => false, 'message' => 'Missing order ID or status.']);
                exit();
            }
            
            $result = updateOrderStatus($conn, $order_id, $new_status, $restaurant_ids, $user_id);
            echo json_encode($result);
            exit();
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
