<?php
// manager/controllers/menu_controller.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/FoodModel.php';
require_once __DIR__ . '/../models/RestaurantModel.php';

require_once __DIR__ . '/../../includes/auth_check.php';

check_auth(['Restaurant Manager']);
require_post();

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category = trim($_POST['category'] ?? '') ?: 'Main Course';
            $status = ($_POST['status'] ?? '') === 'Unavailable' ? 'Unavailable' : 'Available';

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
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category = trim($_POST['category'] ?? '') ?: 'Main Course';
            $status = ($_POST['status'] ?? '') === 'Unavailable' ? 'Unavailable' : 'Available';

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
