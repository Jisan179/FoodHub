<?php
$pageTitle = 'FoodHub - Customer Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p class="page-subtitle">Track your food deliveries, view order history, and explore top restaurants</p>
        </div>
        <div>
            <a href="profile.php" class="btn btn-secondary">👤 My Profile</a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <span>✅</span>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <!-- Summary Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="--card-accent: #ff4757;">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($customer_stats['total_orders']); ?></div>
            <div class="stat-desc">Lifetime orders placed</div>
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Active Orders</div>
            <div class="stat-value"><?php echo number_format($customer_stats['active_orders']); ?></div>
            <div class="stat-desc">Currently being prepared / delivered</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Completed Orders</div>
            <div class="stat-value"><?php echo number_format($customer_stats['completed_orders']); ?></div>
            <div class="stat-desc">Successfully delivered</div>
        </div>

        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Spent</div>
            <div class="stat-value">৳<?php echo number_format($customer_stats['total_spent'], 2); ?></div>
            <div class="stat-desc">Across all completed orders</div>
        </div>
    </div>

    <!-- Active Orders Live Tracker Section -->
    <?php 
    $active_orders_list = array_filter($my_orders, function($o) {
        return in_array($o['order_status'], ['Pending', 'Preparing', 'Ready for Delivery', 'Out for Delivery']);
    });
    ?>

    <?php if (!empty($active_orders_list)): ?>
    <div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">🛵 Live Order Tracking</h2>
            <span class="badge badge-rider"><?php echo count($active_orders_list); ?> In Progress</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Delivery Address</th>
                        <th>Current Status</th>
                        <th>Assigned Rider</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_orders_list as $act): ?>
                    <tr>
                        <td><strong>#<?php echo $act['order_id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($act['restaurant_name']); ?></strong><br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($act['restaurant_phone']); ?></span>
                        </td>
                        <td><strong>৳<?php echo number_format((float)$act['total_amount'], 2); ?></strong></td>
                        <td style="max-width: 200px; font-size: 0.85rem;"><?php echo htmlspecialchars($act['delivery_address']); ?></td>
                        <td>
                            <?php
                            $st = $act['order_status'];
                            $badge = 'badge-pending';
                            if ($st === 'Out for Delivery') $badge = 'badge-rider';
                            elseif ($st === 'Preparing') $badge = 'badge-manager';
                            ?>
                            <span class="badge <?php echo $badge; ?>" style="font-size: 0.85rem;">⏳ <?php echo htmlspecialchars($st); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($act['rider_name'])): ?>
                                <strong>🛵 <?php echo htmlspecialchars($act['rider_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($act['rider_phone'] ?? ''); ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">Searching for nearby rider...</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Partner Restaurants Grid -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">🏪 Explore Partner Restaurants</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php if (!empty($partner_restaurants)): ?>
                    <?php foreach ($partner_restaurants as $rest): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; background: #fafafa; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                                🍽️ <?php echo htmlspecialchars($rest['restaurant_name']); ?>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px; line-height: 1.4;">
                                <?php echo htmlspecialchars($rest['description'] ?? 'Delicious food crafted with love.'); ?>
                            </p>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                📍 <?php echo htmlspecialchars($rest['address']); ?>
                            </div>
                        </div>
                        <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                            <span class="badge badge-active"><?php echo $rest['available_items']; ?> Menu Items</span>
                            <span style="font-size: 0.82rem; color: var(--primary); font-weight: 600;">📞 <?php echo htmlspecialchars($rest['phone']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No restaurants available at this moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order History Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📜 My Order History</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_orders)): ?>
                        <?php foreach ($my_orders as $ord): ?>
                        <tr>
                            <td><strong>#<?php echo $ord['order_id']; ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($ord['restaurant_name']); ?></strong></td>
                            <td><strong>৳<?php echo number_format((float)$ord['total_amount'], 2); ?></strong></td>
                            <td>
                                <span style="font-size: 0.85rem;"><?php echo htmlspecialchars($ord['payment_method']); ?></span><br>
                                <span class="badge <?php echo ($ord['payment_status'] === 'Paid') ? 'badge-delivered' : 'badge-pending'; ?>" style="font-size: 0.72rem;">
                                    <?php echo htmlspecialchars($ord['payment_status']); ?>
                                </span>
                            </td>
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
                            <td style="font-size: 0.82rem; color: var(--text-muted);"><?php echo htmlspecialchars($ord['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                You haven't placed any orders yet. Explore our restaurants above!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
