<?php
// manager/controllers/menu_controller.php
session_start();
require_once '../../config/db.php';
require_once '../models/FoodModel.php';
require_once '../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            $name = htmlspecialchars($_POST['name'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category = htmlspecialchars($_POST['category'] ?? 'Main Course');
            $status = htmlspecialchars($_POST['status'] ?? 'Available');
            
            // Verify ownership
            if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
                $_SESSION['error'] = "Unauthorized access.";
                header('Location: ../views/dashboard.php');
                exit();
            }
            
            if ($price <= 0 || empty($name)) {
                $_SESSION['error'] = "Valid name and price > 0 are required.";
                header("Location: ../views/menu.php?restaurant_id=$restaurant_id");
                exit();
            }
            
            if (insertFood($conn, $restaurant_id, $name, $description, $price, $category, $status)) {
                $_SESSION['success'] = "Food item added successfully.";
            } else {
                $_SESSION['error'] = "Failed to add food item.";
            }
            header("Location: ../views/menu.php?restaurant_id=$restaurant_id");
        }
        break;
        
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = intval($_POST['item_id'] ?? 0);
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            $name = htmlspecialchars($_POST['name'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category = htmlspecialchars($_POST['category'] ?? 'Main Course');
            $status = htmlspecialchars($_POST['status'] ?? 'Available');
            
            // Verify ownership
            if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
                $_SESSION['error'] = "Unauthorized access.";
                header('Location: ../views/dashboard.php');
                exit();
            }
            
            if ($price <= 0 || empty($name)) {
                $_SESSION['error'] = "Valid name and price > 0 are required.";
                header("Location: ../views/menu.php?restaurant_id=$restaurant_id");
                exit();
            }
            
            if (updateFood($conn, $item_id, $restaurant_id, $name, $description, $price, $category, $status)) {
                $_SESSION['success'] = "Food item updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update food item.";
            }
            header("Location: ../views/menu.php?restaurant_id=$restaurant_id");
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = intval($_POST['item_id'] ?? 0);
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            
            // Verify ownership
            if (!getRestaurantByIdAndManager($conn, $restaurant_id, $user_id)) {
                $_SESSION['error'] = "Unauthorized access.";
                header('Location: ../views/dashboard.php');
                exit();
            }
            
            if (softDeleteFood($conn, $item_id, $restaurant_id)) {
                $_SESSION['success'] = "Food item deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete food item.";
            }
            header("Location: ../views/menu.php?restaurant_id=$restaurant_id");
        }
        break;

    default:
        header('Location: ../views/dashboard.php');
        break;
}
