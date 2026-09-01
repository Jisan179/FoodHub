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
        <a href="<?php echo $brand_url; ?>" class="brand-logo">
            <span>🍔 FoodHub</span>
            <span class="brand-badge">Admin Portal</span>
        </a>

        <ul class="nav-menu">
            <li>
                <a href="<?php echo $dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
            </li>

            <?php if ($user_role === 'Administrator'): ?>
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
            <?php endif; ?>

            <li>
                <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                    👤 Profile
                </a>
            </li>
        </ul>
        <?php elseif ($current_role === 'Customer'): ?>
        <ul class="nav-menu">
            <li>
                <a href="<?php echo $dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                    🏠 Home
                </a>
            </li>
            <li>
                <a href="<?php echo $browse_url; ?>" class="nav-link <?php echo ($active_page === 'browse' || $active_page === 'menu') ? 'active' : ''; ?>">
                    🏪 Restaurants
                </a>
            </li>
            <li>
                <a href="<?php echo $fav_url; ?>" class="nav-link <?php echo ($active_page === 'favorites') ? 'active' : ''; ?>">
                    ❤️ Favorites
                </a>
            </li>
            <li>
                <a href="<?php echo $cart_url; ?>" class="nav-link nav-cart-link <?php echo ($active_page === 'cart' || $active_page === 'checkout') ? 'active' : ''; ?>">
                    🛒 Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-pill-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $orders_url; ?>" class="nav-link <?php echo ($active_page === 'orders' || $active_page === 'track') ? 'active' : ''; ?>">
                    📦 My Orders
                </a>
            </li>
            <li>
                <a href="<?php echo $reviews_url; ?>" class="nav-link <?php echo ($active_page === 'reviews') ? 'active' : ''; ?>">
                    ⭐ Reviews
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <div class="nav-user">
            <div class="user-pill">
                <span class="user-avatar"><?php echo strtoupper(substr($admin_username, 0, 1)); ?></span>
                <span><?php echo $admin_username; ?></span>
            </div>
            <a href="<?php echo $logout_url; ?>" class="btn-logout">Logout</a>
        </div>
        <?php else: ?>
        <div class="nav-user">
            <a href="login.php" class="btn btn-secondary btn-sm" style="margin-right: 8px;">Sign In</a>
            <a href="register.php" class="btn btn-primary btn-sm">Register</a>
        </div>
        <?php endif; ?>
    </div>
</nav>
