<?php
require_once __DIR__ . '/../../controllers/admin/dashboard_controller.php';

$pageTitle = 'FoodHub - Admin Dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$orders_page_url = $in_admin_folder ? 'orders.php' : 'admin/orders.php';
$restaurants_page_url = $in_admin_folder ? 'restaurants.php' : 'admin/restaurants.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard Overview</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>! Here is your system performance at a glance.</p>
        </div>
        <div>
            <a href="<?php echo $orders_page_url; ?>" class="btn btn-primary">View All Orders</a>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Users</div>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
            <div class="stat-desc">Registered across all roles</div>
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Pending Approvals</div>
            <div class="stat-value"><?php echo number_format($pending_approvals); ?></div>
            <div class="stat-desc">Restaurants awaiting review</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            <div class="stat-desc">All-time order volume</div>
        </div>

        <div class="stat-card" style="--card-accent: #ff5722;">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value">৳<?php echo number_format((float)$total_revenue, 2); ?></div>
            <div class="stat-desc">From completed & active orders</div>
        </div>
    </div>

    <!-- Pending Restaurants Quick Action -->
    <?php if (!empty($pending_restaurants) && count($pending_restaurants) > 0): ?>
    <div class="card" style="border-left: 4px solid #f59e0b;">
        <div class="card-header">
            <h2 class="card-title">⚠️ Restaurants Awaiting Approval (<?php echo count($pending_restaurants); ?>)</h2>
            <a href="<?php echo $restaurants_page_url; ?>" class="btn btn-secondary btn-sm">Manage All Restaurants</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Restaurant Name</th>
                        <th>Owner / Manager</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_restaurants as $rest): ?>
                    <tr>
                        <td>#<?php echo $rest['restaurant_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($rest['restaurant_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($rest['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($rest['phone']); ?></td>
                        <td><?php echo htmlspecialchars($rest['address']); ?></td>
                        <td>
                            <a href="<?php echo $restaurants_page_url; ?>" class="btn btn-primary btn-sm">Review Status</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Orders Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Orders</h2>
            <a href="<?php echo $orders_page_url; ?>" class="btn btn-secondary btn-sm">View Full Order Book</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_orders) && count($recent_orders) > 0): ?>
                        <?php foreach ($recent_orders as $ord): ?>
                        <tr>
                            <td><strong>#<?php echo $ord['order_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($ord['restaurant_name']); ?></td>
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
                            <td><?php echo htmlspecialchars($ord['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No orders found in the system.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
