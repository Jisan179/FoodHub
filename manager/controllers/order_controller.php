<?php
// manager/controllers/order_controller.php
session_start();

require_once '../../config/db.php';
require_once '../models/OrderModel.php';
require_once '../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    $_SESSION['error'] = "Unauthorized access.";
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$restaurants = getRestaurantsByManager($conn, $user_id);
$restaurant_ids = array_column($restaurants, 'restaurant_id');

if (empty($restaurant_ids)) {
    $_SESSION['error'] = "No restaurants found for this manager.";
    header('Location: ../views/orders.php');
    exit();
}

switch ($action) {
    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order_id = intval($_POST['order_id'] ?? 0);
            $new_status = htmlspecialchars($_POST['status'] ?? '');

            if (!$order_id || !$new_status) {
                $_SESSION['error'] = "Missing order ID or status.";
            } else {
                $result = updateOrderStatus($conn, $order_id, $new_status, $restaurant_ids, $user_id);
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }

            $redirect_url = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : '../views/orders.php';
            header("Location: " . $redirect_url);
            exit();
        }
        break;

    default:
        header('Location: ../views/orders.php');
        break;
}
