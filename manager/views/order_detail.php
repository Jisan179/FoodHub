<?php
// manager/views/order_detail.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['id'] ?? 0);

$restaurants = getRestaurantsByManager($conn, $user_id);
$restaurant_ids = array_column($restaurants, 'restaurant_id');

$order = getOrderByIdAndRestaurants($conn, $order_id, $restaurant_ids);

if (!$order) {
    $_SESSION['error'] = "Order not found or unauthorized.";
    header('Location: orders.php');
    exit();
}

$order_items = getOrderItems($conn, $order_id);

$pageTitle = 'FoodHub - Order #' . $order_id . ' Details';
$currentPage = 'orders';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Order #<?php echo $order_id; ?> Details</h1>
            <p class="page-subtitle">Restaurant: <?php echo htmlspecialchars($order['restaurant_name']); ?></p>
        </div>
        
        <a href="orders.php" class="btn btn-secondary">
            ← Back to Orders
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Order Summary</h2>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 8px;"><strong>Status:</strong> <span class="badge badge-approved"><?php echo htmlspecialchars($order['order_status']); ?></span></p>
                <p style="margin-bottom: 8px;"><strong>Date & Time:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
                <p style="margin-bottom: 8px;"><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?> (<?php echo $order['payment_status']; ?>)</p>
                <p style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin-top: 16px;">Total Amount: ৳<?php echo number_format($order['total_amount'], 2); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Customer Information</h2>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 8px;"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p style="margin-bottom: 8px;"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <p style="margin-bottom: 8px;"><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Ordered Items</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ITEM NAME</th>
                        <th>QUANTITY</th>
                        <th>PRICE</th>
                        <th>SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>৳<?php echo number_format($item['price'], 2); ?></td>
                            <td style="font-weight: 700; color: var(--primary);">৳<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align: right; font-weight: 800;">Total:</th>
                        <th style="font-size: 1.1rem; color: var(--primary); font-weight: 800;">৳<?php echo number_format($order['total_amount'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
