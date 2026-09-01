<?php
// manager/views/orders.php
session_start();
require_once '../../config/db.php';
require_once '../models/OrderModel.php';
require_once '../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$restaurants = getRestaurantsByManager($conn, $user_id);
$restaurant_ids = array_column($restaurants, 'restaurant_id');

$all_orders = getOrdersByRestaurants($conn, $restaurant_ids);

$new_orders = [];
$kitchen_orders = [];

foreach ($all_orders as $o) {
    if ($o['order_status'] === 'Pending') {
        $new_orders[] = $o;
    } elseif (in_array($o['order_status'], ['Preparing', 'Ready for Delivery'])) {
        $kitchen_orders[] = $o;
    }
}

$pageTitle = 'FoodHub - Orders & Deliveries';
$currentPage = 'orders';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Orders & Deliveries</h1>
            <p class="page-subtitle">Accept incoming orders and manage food preparation status.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span>✅</span>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
        
        <!-- Pending Orders Column -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">New Pending Orders (<?php echo count($new_orders); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($new_orders)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 30px;">No new pending orders right now.</p>
                    <?php else: ?>
                        <?php foreach ($new_orders as $order): 
                            $items = getOrderItems($conn, $order['order_id']);
                        ?>
                            <div style="background: #ffffff; border-left: 4px solid var(--primary); border-radius: 8px; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 18px; margin-bottom: 16px; box-shadow: var(--shadow-sm);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-main);">Order #<?php echo $order['order_id']; ?></span>
                                    <span style="font-weight: 800; font-size: 1.2rem; color: var(--primary);">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                                    Customer: <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong> | <?php echo htmlspecialchars($order['payment_method']); ?>
                                </p>

                                <div style="background: #f8fafc; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
                                    <?php foreach ($items as $item): ?>
                                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; margin-bottom: 4px;">
                                            <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['item_name']); ?></span>
                                            <span>৳<?php echo number_format($item['subtotal'], 2); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div style="display: flex; gap: 10px;">
                                    <form action="../controllers/order_controller.php?action=update_status" method="POST" style="flex: 2;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="status" value="Preparing">
                                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                                            Accept Order
                                        </button>
                                    </form>
                                    
                                    <form action="../controllers/order_controller.php?action=update_status" method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to reject this order?');">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="status" value="Cancelled">
                                        <button type="submit" class="btn btn-danger" style="width: 100%;">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- In Kitchen / Ready Column -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">In Kitchen (<?php echo count($kitchen_orders); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($kitchen_orders)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 30px;">No orders currently preparing.</p>
                    <?php else: ?>
                        <?php foreach ($kitchen_orders as $k_order): 
                            $is_ready = ($k_order['order_status'] === 'Ready for Delivery');
                        ?>
                            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 14px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;">
                                    <span>Order #<?php echo $k_order['order_id']; ?></span>
                                    <span class="badge <?php echo $is_ready ? 'badge-approved' : 'badge-pending'; ?>">
                                        <?php echo htmlspecialchars($k_order['order_status']); ?>
                                    </span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                                    Customer: <?php echo htmlspecialchars($k_order['customer_name']); ?>
                                </p>
                                <?php if (!$is_ready): ?>
                                    <form action="../controllers/order_controller.php?action=update_status" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $k_order['order_id']; ?>">
                                        <input type="hidden" name="status" value="Ready for Delivery">
                                        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                                            Mark as Ready for Delivery
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled style="width: 100%; opacity: 0.7;">
                                        Waiting for Rider Pickup
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../../assets/js/manager.js"></script>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
