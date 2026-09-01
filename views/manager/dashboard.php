<?php
if (!isset($manager_stats)) {
    require_once __DIR__ . '/../../controllers/dashboard_controller.php';
}

$pageTitle = 'FoodHub - Restaurant Manager Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <?php echo $my_restaurant ? htmlspecialchars($my_restaurant['name']) : 'Restaurant Management'; ?> 🏪
            </h1>
            <p class="page-subtitle">Welcome, <?php echo htmlspecialchars($user_name); ?>! Manage kitchen orders, monitor food items, and track restaurant revenues</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <?php if ($my_restaurant): ?>
                <span class="badge badge-active" style="font-size: 0.9rem; padding: 8px 16px;">Status: <?php echo htmlspecialchars($my_restaurant['status']); ?></span>
                <a href="<?php echo $root_url; ?>manager/views/menu.php?restaurant_id=<?php echo $my_restaurant['restaurant_id']; ?>" class="btn btn-primary btn-sm">📋 Manage Menu</a>
            <?php endif; ?>
            <a href="<?php echo $root_url; ?>manager/views/register_restaurant.php" class="btn btn-secondary btn-sm">➕ Register Restaurant</a>
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
        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Incoming Orders</div>
            <div class="stat-value"><?php echo number_format($manager_stats['incoming_orders']); ?></div>
            <div class="stat-desc">Awaiting prep / ready for pickup</div>
        </div>

        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($manager_stats['total_orders']); ?></div>
            <div class="stat-desc">All-time customer requests</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value">৳<?php echo number_format($manager_stats['total_revenue'], 2); ?></div>
            <div class="stat-desc">From fulfilled deliveries</div>
        </div>

        <div class="stat-card" style="--card-accent: #8b5cf6;">
            <div class="stat-title">Menu Items</div>
            <div class="stat-value"><?php echo number_format($manager_stats['total_items']); ?></div>
            <div class="stat-desc">Active in your catalog</div>
        </div>
    </div>

    <!-- Active Kitchen Orders Management Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">🍳 Incoming & Active Orders</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Details</th>
                        <th>Amount</th>
                        <th>Delivery Address</th>
                        <th>Status</th>
                        <th>Rider Assigned</th>
                        <th>Kitchen Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($restaurant_orders)): ?>
                        <?php foreach ($restaurant_orders as $ord): ?>
                        <tr>
                            <td><strong>#<?php echo $ord['order_id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($ord['customer_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($ord['customer_phone'] ?? ''); ?></span>
                            </td>
                            <td><strong>৳<?php echo number_format((float)$ord['total_amount'], 2); ?></strong></td>
                            <td style="max-width: 180px; font-size: 0.82rem;"><?php echo htmlspecialchars($ord['delivery_address']); ?></td>
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
                                <?php if (!empty($ord['rider_name'])): ?>
                                    <strong><?php echo htmlspecialchars($ord['rider_name']); ?></strong>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
                                    
                                    <select name="new_status" class="form-control form-control-sm" style="width: auto; padding: 4px 8px; font-size: 0.8rem;">
                                        <option value="Pending" <?php echo ($ord['order_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Preparing" <?php echo ($ord['order_status'] === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="Ready for Delivery" <?php echo ($ord['order_status'] === 'Ready for Delivery') ? 'selected' : ''; ?>>Ready for Delivery</option>
                                        <option value="Delivered" <?php echo ($ord['order_status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Cancelled" <?php echo ($ord['order_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.8rem;">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No orders received yet for this restaurant.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Menu Items Catalog Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📋 Restaurant Menu Items (<?php echo count($menu_items); ?>)</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Availability</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($menu_items)): ?>
                        <?php foreach ($menu_items as $item): ?>
                        <tr>
                            <td>#<?php echo $item['item_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td><span class="badge badge-manager"><?php echo htmlspecialchars($item['category']); ?></span></td>
                            <td><strong>৳<?php echo number_format((float)$item['price'], 2); ?></strong></td>
                            <td style="max-width: 250px; font-size: 0.82rem; color: var(--text-muted);"><?php echo htmlspecialchars($item['description'] ?? '—'); ?></td>
                            <td>
                                <span class="badge <?php echo ($item['status'] === 'Available') ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No menu items found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
