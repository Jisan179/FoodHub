<?php
if (!isset($rider_stats)) {
    require_once __DIR__ . '/../../controllers/dashboard_controller.php';
}

$pageTitle = 'FoodHub - Rider Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Rider Portal 🛵</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($user_name); ?>! Review available delivery tasks, manage active trips, and check your earnings</p>
        </div>
        <div>
            <span class="badge badge-rider" style="font-size: 0.9rem; padding: 8px 16px;">🛵 On Duty</span>
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
            <div class="stat-title">Available Requests</div>
            <div class="stat-value"><?php echo count($available_deliveries); ?></div>
            <div class="stat-desc">Waiting to be picked up</div>
        </div>

        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Active Trips</div>
            <div class="stat-value"><?php echo number_format($rider_stats['active_deliveries']); ?></div>
            <div class="stat-desc">Assigned & on the road</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Delivered Trips</div>
            <div class="stat-value"><?php echo number_format($rider_stats['completed_deliveries']); ?></div>
            <div class="stat-desc">Completed successfully</div>
        </div>

        <div class="stat-card" style="--card-accent: #8b5cf6;">
            <div class="stat-title">Estimated Earnings</div>
            <div class="stat-value">৳<?php echo number_format($rider_stats['total_earnings'], 2); ?></div>
            <div class="stat-desc">Delivery commissions earned</div>
        </div>
    </div>

    <!-- Section 1: Available Orders to Claim -->
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid #f59e0b;">
        <div class="card-header">
            <h2 class="card-title">📦 Available Delivery Orders (<?php echo count($available_deliveries); ?>)</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant / Pickup</th>
                        <th>Customer / Drop-off</th>
                        <th>Order Value</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($available_deliveries)): ?>
                        <?php foreach ($available_deliveries as $avail): ?>
                        <tr>
                            <td><strong>#<?php echo $avail['order_id']; ?></strong></td>
                            <td>
                                <strong>🏪 <?php echo htmlspecialchars($avail['restaurant_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">📍 <?php echo htmlspecialchars($avail['restaurant_address']); ?></span>
                            </td>
                            <td>
                                <strong>👤 <?php echo htmlspecialchars($avail['customer_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">📍 <?php echo htmlspecialchars($avail['delivery_address']); ?></span>
                            </td>
                            <td><strong>৳<?php echo number_format((float)$avail['total_amount'], 2); ?></strong></td>
                            <td><span class="badge badge-manager"><?php echo htmlspecialchars($avail['order_status']); ?></span></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                    <input type="hidden" name="action" value="claim_delivery">
                                    <input type="hidden" name="order_id" value="<?php echo $avail['order_id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Accept Delivery</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 25px;">
                                No unassigned orders currently available. Check back in a few minutes!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: My Active & Assigned Deliveries -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🛵 My Assigned Deliveries</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Trip ID</th>
                        <th>Order</th>
                        <th>Pickup From</th>
                        <th>Deliver To</th>
                        <th>Payment</th>
                        <th>Delivery Status</th>
                        <th>Update Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_deliveries)): ?>
                        <?php foreach ($my_deliveries as $deliv): ?>
                        <tr>
                            <td><strong>#D-<?php echo $deliv['delivery_id']; ?></strong></td>
                            <td>#<?php echo $deliv['order_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($deliv['restaurant_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">📞 <?php echo htmlspecialchars($deliv['restaurant_phone']); ?></span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($deliv['customer_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($deliv['delivery_address']); ?></span>
                            </td>
                            <td>
                                <strong>৳<?php echo number_format((float)$deliv['total_amount'], 2); ?></strong><br>
                                <span class="badge <?php echo ($deliv['payment_status'] === 'Paid') ? 'badge-delivered' : 'badge-pending'; ?>" style="font-size: 0.72rem;">
                                    <?php echo htmlspecialchars($deliv['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $ds = $deliv['delivery_status'];
                                $badgeClass = 'badge-pending';
                                if ($ds === 'Delivered') $badgeClass = 'badge-delivered';
                                elseif ($ds === 'Picked Up' || $ds === 'Assigned') $badgeClass = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($ds); ?></span>
                            </td>
                            <td>
                                <?php if ($deliv['delivery_status'] !== 'Delivered'): ?>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: flex; gap: 6px;">
                                        <input type="hidden" name="action" value="update_delivery_status">
                                        <input type="hidden" name="delivery_id" value="<?php echo $deliv['delivery_id']; ?>">
                                        
                                        <?php if ($deliv['delivery_status'] === 'Assigned'): ?>
                                            <input type="hidden" name="status" value="Picked Up">
                                            <button type="submit" class="btn btn-secondary btn-sm">Mark Picked Up</button>
                                        <?php elseif ($deliv['delivery_status'] === 'Picked Up'): ?>
                                            <input type="hidden" name="status" value="Delivered">
                                            <button type="submit" class="btn btn-success btn-sm" style="background: #10b981; color: #fff; border: none;">Mark Delivered</button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.82rem; color: #10b981; font-weight: 600;">✅ Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                You have not claimed any deliveries yet. Accept a request above to begin.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
