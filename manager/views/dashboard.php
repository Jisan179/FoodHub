<?php
// manager/views/dashboard.php
session_start();
require_once '../../config/db.php';
require_once '../models/RestaurantModel.php';
require_once '../models/OrderModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$manager_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Manager';

// Fetch manager restaurants
$restaurants = getRestaurantsByManager($conn, $user_id);
$restaurant_ids = array_column($restaurants, 'restaurant_id');

// Compute metrics
$total_restaurants = count($restaurants);
$pending_approvals = 0;
foreach ($restaurants as $r) {
    if ($r['status'] === 'Pending') {
        $pending_approvals++;
    }
}

$all_orders = getOrdersByRestaurants($conn, $restaurant_ids);
$total_orders = count($all_orders);
$total_revenue = 0;
foreach ($all_orders as $o) {
    if ($o['order_status'] !== 'Cancelled') {
        $total_revenue += floatval($o['total_amount']);
    }
}

$pageTitle = 'FoodHub - Restaurant Manager Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
?>

<div class="main-wrapper">

    <div class="page-header">
        <div>
            <h1 class="page-title">Manager Overview</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($manager_name); ?>! Here is your restaurant performance at a glance.</p>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="register_restaurant.php" class="btn btn-primary">
                ➕ Register Restaurant
            </a>
            <a href="orders.php" class="btn btn-secondary">
                📦 View Live Orders
            </a>
        </div>
    </div>

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

    <!-- Summary Statistics Cards -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Restaurants</div>
            <div class="stat-value"><?php echo number_format($total_restaurants); ?></div>
            <div class="stat-desc">Registered under your account</div>
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Pending Approvals</div>
            <div class="stat-value"><?php echo number_format($pending_approvals); ?></div>
            <div class="stat-desc">Restaurants awaiting admin review</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            <div class="stat-desc">All-time order volume</div>
        </div>

        <div class="stat-card" style="--card-accent: #ff4757;">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value">৳<?php echo number_format($total_revenue, 2); ?></div>
            <div class="stat-desc">From completed & active orders</div>
        </div>
    </div>

    <!-- Restaurants Data Table Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Your Restaurants</h2>
            <a href="register_restaurant.php" class="btn btn-sm btn-primary">+ Add New Restaurant</a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>RESTAURANT ID</th>
                        <th>NAME</th>
                        <th>CUISINE</th>
                        <th>STATUS</th>
                        <th>AVAILABILITY</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($restaurants)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No restaurants found. Click <strong>Add New Restaurant</strong> above to register one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($restaurants as $res): ?>
                            <tr>
                                <td><strong>#<?php echo $res['restaurant_id']; ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($res['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($res['cuisine_type'] ?? 'General'); ?></td>
                                <td>
                                    <?php 
                                    $st = $res['status'];
                                    $badgeClass = 'badge-pending';
                                    if ($st === 'Approved') $badgeClass = 'badge-approved';
                                    elseif ($st === 'Rejected' || $st === 'Suspended') $badgeClass = 'badge-rejected';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st); ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $res['is_open'] ? 'badge-available' : 'badge-suspended'; ?>">
                                        <?php echo $res['is_open'] ? 'Open' : 'Closed'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a href="restaurant_profile.php?id=<?php echo $res['restaurant_id']; ?>" class="btn btn-sm btn-secondary">
                                            Edit Profile
                                        </a>
                                        <?php if ($res['status'] === 'Approved'): ?>
                                            <a href="menu.php?restaurant_id=<?php echo $res['restaurant_id']; ?>" class="btn btn-sm btn-primary">
                                                Manage Menu
                                            </a>
                                        <?php endif; ?>
                                        <form action="../controllers/restaurant_controller.php?action=delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this restaurant? This action cannot be undone.');">
                                            <input type="hidden" name="restaurant_id" value="<?php echo $res['restaurant_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
