<?php
// customer/dashboard.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Customer') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$customer_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Customer';

// Fetch active restaurants
$res_query = "SELECT * FROM restaurants WHERE status = 'Approved' ORDER BY created_at DESC";
$restaurants_res = mysqli_query($conn, $res_query);
$restaurants = mysqli_fetch_all($restaurants_res, MYSQLI_ASSOC);

// Fetch recent customer orders
$orders_query = "SELECT o.*, r.name as restaurant_name FROM orders o JOIN restaurants r ON o.restaurant_id = r.restaurant_id WHERE o.customer_id = ? ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $orders_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$my_orders = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - FoodHub</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <header class="admin-navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="brand-logo">
                🍔 FoodHub <span class="brand-badge" style="background: #10B981;">CUSTOMER PORTAL</span>
            </a>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link active">🏠 Home</a></li>
                <li><a href="#restaurants" class="nav-link">🏪 Restaurants</a></li>
                <li><a href="#my-orders" class="nav-link">📦 My Orders</a></li>
            </ul>
            <div class="nav-user">
                <div class="user-pill">
                    <div class="user-avatar" style="background: #10B981;">C</div>
                    <span><?php echo htmlspecialchars($customer_name); ?></span>
                </div>
                <a href="../logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <div class="main-wrapper">
        <div class="page-header">
            <div>
                <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($customer_name); ?>!</h1>
                <p class="page-subtitle">Browse partner restaurants and track your food orders.</p>
            </div>
        </div>

        <!-- Available Restaurants -->
        <div class="card" id="restaurants">
            <div class="card-header">
                <h2 class="card-title">Explore Restaurants</h2>
            </div>
            <div class="card-body">
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php if (empty($restaurants)): ?>
                        <p style="color: #64748B;">No active restaurants available right now.</p>
                    <?php else: ?>
                        <?php foreach ($restaurants as $res): ?>
                            <div class="stat-card" style="--card-accent: #ff5722;">
                                <div class="stat-title"><?php echo htmlspecialchars($res['cuisine_type'] ?? 'Restaurant'); ?></div>
                                <div class="stat-value" style="font-size: 1.25rem; margin: 6px 0;"><?php echo htmlspecialchars($res['name']); ?></div>
                                <div class="stat-desc" style="margin-bottom: 12px;"><?php echo htmlspecialchars($res['address']); ?></div>
                                <span class="badge badge-approved">Open for Orders</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="card" id="my-orders">
            <div class="card-header">
                <h2 class="card-title">My Order History</h2>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Restaurant</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_orders)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748B;">No orders placed yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($my_orders as $order): ?>
                                <tr>
                                    <td style="font-weight: 700;">#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                                    <td style="font-weight: 700; color: #ff5722;">৳<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower(str_replace(' ', '', $order['order_status'])); ?>">
                                            <?php echo htmlspecialchars($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $order['created_at']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
