<?php
require_once __DIR__ . '/../auth/rider_check.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/rider_model.php';

$rider_id = intval($_SESSION['user_id']);
$is_json = isset($_GET['format']) && $_GET['format'] === 'json';

if ($is_json && isset($_GET['history_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['history' => get_delivery_history($conn, intval($_GET['history_id']), $rider_id)]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = trim($_POST['action'] ?? '');
    $delivery_id = intval($_POST['delivery_id'] ?? 0);
    $result = rider_update_delivery($conn, $delivery_id, $rider_id, $action, $_POST['note'] ?? '');
    http_response_code($result['ok'] ? 200 : 422);
    echo json_encode($result);
    exit();
}

$active_deliveries = get_rider_deliveries($conn, $rider_id);
$history_deliveries = get_rider_deliveries($conn, $rider_id, true);
$rider_summary = get_rider_summary($conn, $rider_id);
if ($is_json) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['active' => $active_deliveries, 'history' => $history_deliveries]);
    exit();
}
$currentPage = 'rider';
