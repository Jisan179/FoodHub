<?php
// Shared Admin Navigation Bar Component
$active_page = $currentPage ?? 'dashboard';
$admin_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$in_views_folder = (strpos($_SERVER['PHP_SELF'], '/views/admin/') !== false);

if ($in_admin_folder) {
    $dash_url = 'dashboard.php';
    $users_url = 'users.php';
    $rest_url = 'restaurants.php';
    $orders_url = 'orders.php';
    $logout_url = '../logout.php';
} elseif ($in_views_folder) {
    $dash_url = 'dashboard.php';
    $users_url = 'users.php';
    $rest_url = 'restaurants.php';
    $orders_url = 'orders.php';
    $logout_url = '../../logout.php';
} else {
    $dash_url = 'admin/dashboard.php';
    $users_url = 'admin/users.php';
    $rest_url = 'admin/restaurants.php';
    $orders_url = 'admin/orders.php';
    $logout_url = 'logout.php';
}
?>
<nav class="admin-navbar">
    <div class="nav-container">
        <a href="<?php echo $dash_url; ?>" class="brand-logo">
            <span>🍔 FoodHub</span>
            <span class="brand-badge">Admin Portal</span>
        </a>

        <ul class="nav-menu">
            <li>
                <a href="<?php echo $dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
            </li>
            <li>
                <a href="<?php echo $users_url; ?>" class="nav-link <?php echo ($active_page === 'users') ? 'active' : ''; ?>">
                    👥 Users
                </a>
            </li>
            <li>
                <a href="<?php echo $rest_url; ?>" class="nav-link <?php echo ($active_page === 'restaurants') ? 'active' : ''; ?>">
                    🏪 Restaurants
                </a>
            </li>
            <li>
                <a href="<?php echo $orders_url; ?>" class="nav-link <?php echo ($active_page === 'orders') ? 'active' : ''; ?>">
                    📦 Orders & Deliveries
                </a>
            </li>
        </ul>

        <div class="nav-user">
            <div class="user-pill">
                <span class="user-avatar"><?php echo strtoupper(substr($admin_username, 0, 1)); ?></span>
                <span><?php echo $admin_username; ?></span>
            </div>
            <a href="<?php echo $logout_url; ?>" class="btn-logout">Logout</a>
        </div>
    </div>
</nav>
