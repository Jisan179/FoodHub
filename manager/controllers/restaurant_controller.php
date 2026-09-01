<?php
// manager/controllers/restaurant_controller.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = htmlspecialchars($_POST['name'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $address = htmlspecialchars($_POST['address'] ?? '');
            $phone = htmlspecialchars($_POST['phone'] ?? '');
            $cuisine_type = htmlspecialchars($_POST['cuisine_type'] ?? '');

            if (empty($name) || empty($address)) {
                $_SESSION['error'] = "Name and Address are required.";
                header('Location: ../views/register_restaurant.php');
                exit();
            }

            $restaurant_id = insertRestaurant($conn, $user_id, $name, $description, $address, $phone, $cuisine_type);

            if ($restaurant_id) {
                $_SESSION['success'] = "Restaurant registered successfully and is pending approval.";
                header('Location: ../views/dashboard.php');
            } else {
                $_SESSION['error'] = "Failed to register restaurant.";
                header('Location: ../views/register_restaurant.php');
            }
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            $name = htmlspecialchars($_POST['name'] ?? '');
            $description = htmlspecialchars($_POST['description'] ?? '');
            $address = htmlspecialchars($_POST['address'] ?? '');
            $phone = htmlspecialchars($_POST['phone'] ?? '');
            $cuisine_type = htmlspecialchars($_POST['cuisine_type'] ?? '');

            if (updateRestaurant($conn, $restaurant_id, $user_id, $name, $description, $address, $phone, $cuisine_type)) {
                $_SESSION['success'] = "Restaurant updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update restaurant.";
            }
            header("Location: ../views/restaurant_profile.php?id=$restaurant_id");
        }
        break;

    case 'toggle_availability':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $restaurant_id = intval($_POST['restaurant_id'] ?? 0);
            $is_open = intval($_POST['is_open'] ?? 0);

            if (toggleRestaurantAvailability($conn, $restaurant_id, $user_id, $is_open)) {
                $status_str = $is_open ? "Open for orders" : "Closed";
                $_SESSION['success'] = "Restaurant availability updated to $status_str.";
            } else {
                $_SESSION['error'] = "Failed to toggle availability.";
            }
            header('Location: ../views/dashboard.php');
            exit();
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
            $restaurant_id = intval($_REQUEST['restaurant_id'] ?? $_REQUEST['id'] ?? 0);
            if (deleteRestaurant($conn, $restaurant_id, $user_id)) {
                $_SESSION['success'] = "Restaurant deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete restaurant or unauthorized.";
            }
            header("Location: ../views/dashboard.php");
            exit();
        }
        break;

    default:
        header('Location: ../views/dashboard.php');
        break;
}
