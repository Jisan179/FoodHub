<?php
require_once __DIR__ . '/../../controllers/customer/order_history_controller.php';

$pageTitle = 'FoodHub - My Order History';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <span>✅</span>
            <span><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1 class="page-title">📦 My Orders</h1>
            <p class="page-subtitle">Track your current deliveries and browse your past ordering history.</p>
        </div>
        <a href="browse_restaurants.php" class="btn btn-primary btn-sm">+ Order New Food</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Order Status</th>
                        <th>Delivery Status</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td><strong>#<?php echo $ord['order_id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($ord['restaurant_name']); ?></strong>
                                <?php if (!empty($ord['restaurant_phone'])): ?>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($ord['restaurant_phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $ord['total_items']; ?> items</td>
                            <td><strong>৳<?php echo number_format((float)$ord['total_amount'], 2); ?></strong></td>
                            <td>
                                <?php
                                $st = $ord['order_status'];
                                $badgeClass = 'badge-pending';
                                if ($st === 'Delivered') $badgeClass = 'badge-delivered';
                                elseif ($st === 'Cancelled') $badgeClass = 'badge-cancelled';
                                elseif ($st === 'Preparing' || $st === 'Out for Delivery' || $st === 'Ready for Delivery') $badgeClass = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st); ?></span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($ord['delivery_status'] ?? 'Pending Assignment'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($ord['created_at']); ?></td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                    <a href="order_track.php?order_id=<?php echo $ord['order_id']; ?>" class="btn btn-secondary btn-sm">
                                        Details & Track
                                    </a>
                                    <?php if ($st === 'Delivered'): ?>
                                        <a href="reviews.php" class="btn btn-primary btn-sm" style="background: #f59e0b; border-color: #f59e0b;">
                                            ⭐ Review
                                        </a>
                                    <?php elseif ($st === 'Pending' || $st === 'Preparing'): ?>
                                        <form action="actions/cancel_order.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel order #<?php echo $ord['order_id']; ?>?');" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
                                            <input type="hidden" name="redirect_url" value="order_history.php">
                                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                <div style="font-size: 2rem; margin-bottom: 8px;">📦</div>
                                <div>No orders found. You haven't placed any orders yet!</div>
                                <div style="margin-top: 12px;">
                                    <a href="browse_restaurants.php" class="btn btn-primary btn-sm">Start Ordering</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
