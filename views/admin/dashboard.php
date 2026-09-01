<?php
require_once __DIR__ . '/../../controllers/admin/dashboard_controller.php';

$pageTitle = 'FoodHub - Admin Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$orders_page_url = $in_admin_folder ? 'orders.php' : 'admin/orders.php';
$restaurants_page_url = $in_admin_folder ? 'restaurants.php' : 'admin/restaurants.php';
$users_page_url = $in_admin_folder ? 'users.php' : 'admin/users.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Administrator Overview</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin'); ?>! Here is your platform performance at a glance.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?php echo $users_page_url; ?>" class="btn btn-secondary">👥 User Management</a>
            <a href="<?php echo $orders_page_url; ?>" class="btn btn-primary">📦 View All Orders</a>
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
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Users</div>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
            <div class="stat-desc">
                <?php echo $role_counts['Customer'] ?? 0; ?> Customers, <?php echo $role_counts['Restaurant Manager'] ?? 0; ?> Managers, <?php echo $role_counts['Rider'] ?? 0; ?> Riders
            </div>
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

        <div class="stat-card" style="--card-accent: #ff4757;">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value">৳<?php echo number_format((float)$total_revenue, 2); ?></div>
            <div class="stat-desc">From completed & active orders</div>
        </div>
    </div>

    <!-- User Roles Breakdown & Quick Access Bar -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">👥 User Accounts Distribution</h2>
            <a href="<?php echo $users_page_url; ?>" class="btn btn-secondary btn-sm">Manage All Users →</a>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
                <div style="padding: 14px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <div style="font-size: 0.8rem; color: #1e40af; font-weight: 600;">👑 Administrators</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #1e3a8a; margin-top: 4px;"><?php echo $role_counts['Administrator'] ?? 0; ?></div>
                </div>
                <div style="padding: 14px; background: #ecfdf5; border-radius: 8px; border-left: 4px solid #10b981;">
                    <div style="font-size: 0.8rem; color: #065f46; font-weight: 600;">🛒 Customers</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #064e3b; margin-top: 4px;"><?php echo $role_counts['Customer'] ?? 0; ?></div>
                </div>
                <div style="padding: 14px; background: #fff7ed; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <div style="font-size: 0.8rem; color: #9a3412; font-weight: 600;">🏪 Restaurant Managers</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #7c2d12; margin-top: 4px;"><?php echo $role_counts['Restaurant Manager'] ?? 0; ?></div>
                </div>
                <div style="padding: 14px; background: #faf5ff; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                    <div style="font-size: 0.8rem; color: #5b21b6; font-weight: 600;">🛵 Delivery Riders</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #4c1d95; margin-top: 4px;"><?php echo $role_counts['Rider'] ?? 0; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Restaurants Quick Action -->
    <?php if (!empty($pending_restaurants) && count($pending_restaurants) > 0): ?>
    <div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 24px;">
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
