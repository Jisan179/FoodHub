<?php
/**
 * FoodHub - Rider Delivery Action Controller
 */

require_once __DIR__ . '/../../rider/models/RiderModel.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';

check_auth(['Rider']);

$user = get_logged_user();
$rider_id = $user['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_id = intval($_POST['delivery_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');
    $note = trim($_POST['note'] ?? '');

    $result = rider_update_delivery($conn, $delivery_id, $rider_id, $action, $note);

    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    if ($result['ok']) {
        $_SESSION['flash_success'] = $result['message'];
    } else {
        $_SESSION['flash_error'] = $result['message'];
    }

    header("Location: ../dashboard.php");
    exit();
}
