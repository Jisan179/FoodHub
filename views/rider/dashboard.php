<?php
require_once __DIR__ . '/../../controllers/rider/dashboard_controller.php';
$pageTitle = 'FoodHub - Rider Dashboard';
require_once __DIR__ . '/../partials/header.php';
?>
<nav class="admin-navbar">
    <div class="nav-container">
        <a href="dashboard.php" class="brand-logo"><span>🍔 FoodHub</span><span class="brand-badge">Rider Portal</span></a>
        <ul class="nav-menu">
            <li><a href="#dashboard" class="nav-link active">📊 Dashboard</a></li>
            <li><a href="#live-orders" class="nav-link">📦 Deliveries</a></li>
            <li><a href="#earnings" class="nav-link">📈 History</a></li>
        </ul>
        <div class="nav-user">
            <button type="button" class="btn btn-secondary btn-sm" data-online-toggle>Go Offline</button><div class="user-pill"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['name'] ?? 'R', 0, 1)); ?></span><span><?php echo htmlspecialchars($_SESSION['name'] ?? 'Rider'); ?></span></div><a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</nav>
<main class="main-wrapper" id="dashboard" data-rider-dashboard>
    <div class="page-header">
        <div><h1 class="page-title">Rider Dashboard</h1><p class="page-subtitle">You are currently <strong class="online-text">Online</strong> and receiving orders.</p></div>
    </div>
    <div class="alert" data-feedback hidden></div>
    <section class="stats-grid"><div class="stat-card"><div class="stat-title">Active deliveries</div><div class="stat-value" data-active-count><?php echo intval($rider_summary['active_count']); ?></div><div class="stat-desc">Available and assigned to you</div></div><div class="stat-card"><div class="stat-title">Completed deliveries</div><div class="stat-value"><?php echo intval($rider_summary['history_count']); ?></div><div class="stat-desc">Your delivery history</div></div><div class="stat-card"><div class="stat-title">Estimated earnings</div><div class="stat-value">৳<?php echo number_format((float)$rider_summary['total_earnings'], 2); ?></div><div class="stat-desc">From completed deliveries</div></div></section>
    <section class="card" id="live-orders"><div class="card-header"><h2 class="card-title">Active Deliveries &amp; Available Offers</h2><button type="button" class="btn btn-secondary btn-sm" data-refresh>↻ Refresh</button></div><div class="table-responsive"><table class="data-table"><thead><tr><th>Order</th><th>Restaurant</th><th>Customer</th><th>Drop-off Address</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($active_deliveries as $delivery): ?>
            <?php require __DIR__ . '/delivery_row.php'; ?>
        <?php endforeach; ?>
        <?php if (!$active_deliveries): ?><tr><td colspan="6" class="empty-state">No active deliveries or available offers.</td></tr><?php endif; ?>
    </tbody></table></div></section>
    <section class="card" id="earnings"><div class="card-header"><h2 class="card-title">Delivery History</h2></div><div class="table-responsive"><table class="data-table"><thead><tr><th>Order</th><th>Restaurant</th><th>Customer</th><th>Status</th><th>Completed</th><th>Details</th></tr></thead><tbody>
        <?php foreach ($history_deliveries as $delivery): ?>
            <tr><td><strong>#<?php echo intval($delivery['order_id']); ?></strong></td><td><?php echo htmlspecialchars($delivery['restaurant_name']); ?></td><td><?php echo htmlspecialchars($delivery['customer_name']); ?></td><td><span class="badge <?php echo $delivery['delivery_status'] === 'Delivered' ? 'badge-delivered' : 'badge-cancelled'; ?>"><?php echo htmlspecialchars($delivery['delivery_status']); ?></span></td><td><?php echo $delivery['delivered_at'] ? htmlspecialchars(date('M d, Y H:i', strtotime($delivery['delivered_at']))) : '-'; ?></td><td><button type="button" class="btn btn-sm btn-secondary" data-history="<?php echo intval($delivery['delivery_id']); ?>">View history</button></td></tr>
        <?php endforeach; ?>
        <?php if (!$history_deliveries): ?><tr><td colspan="6" class="empty-state">No delivery history yet.</td></tr><?php endif; ?></tbody></table></div></section></main>
<script src="../assets/js/rider.js"></script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
