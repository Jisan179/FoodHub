<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth_check.php';

$active_page = $currentPage ?? 'dashboard';
$is_logged = is_logged_in();
$current_role = normalize_role($_SESSION['role'] ?? 'Guest');
$display_username = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User');

$root_url     = get_foodhub_root_path();
$profile_url  = $root_url . 'profile.php';
$logout_url   = $root_url . 'logout.php';
$login_url    = $root_url . 'login.php';
$register_url = $root_url . 'register.php';
$brand_url    = $is_logged ? get_user_dashboard_url($current_role) : $login_url;

// Admin URLs
$admin_dash_url   = $root_url . 'admin/dashboard.php';
$admin_users_url  = $root_url . 'admin/users.php';
$admin_rest_url   = $root_url . 'admin/restaurants.php';
$admin_orders_url = $root_url . 'admin/orders.php';

// Customer URLs
$cust_dash_url    = $root_url . 'customer/dashboard.php';
$cust_browse_url  = $root_url . 'customer/browse_restaurants.php';
$cust_fav_url     = $root_url . 'customer/favorites.php';
$cust_cart_url    = $root_url . 'customer/cart.php';
$cust_orders_url  = $root_url . 'customer/order_history.php';
$cust_reviews_url = $root_url . 'customer/reviews.php';

// Manager URLs
$mgr_dash_url     = $root_url . 'manager/views/dashboard.php';
$mgr_orders_url   = $root_url . 'manager/views/orders.php';
$mgr_register_url = $root_url . 'manager/views/register_restaurant.php';

// Rider URLs
$rider_dash_url   = $root_url . 'rider/dashboard.php';

// Calculate Cart Items for Customer
$cart_count = 0;
if ($is_logged && $current_role === 'Customer') {
    if (isset($conn) && $conn) {
        $cust_uid = intval($_SESSION['user_id']);
        $cq = mysqli_query($conn, "SELECT SUM(quantity) AS total_qty FROM cart WHERE customer_id = $cust_uid");
        if ($cq && $crow = mysqli_fetch_assoc($cq)) {
            $cart_count = intval($crow['total_qty'] ?? 0);
        }
    }
}
?>
<nav class="admin-navbar <?php echo ($current_role === 'Customer') ? 'customer-navbar' : ''; ?>">
    <div class="nav-container">
        <a href="<?php echo $brand_url; ?>" class="brand-logo">
            <span>🍔 FoodHub</span>
            <?php if ($current_role === 'Administrator'): ?>
                <span class="brand-badge">Admin Portal</span>
            <?php elseif ($current_role === 'Customer'): ?>
                <span class="brand-badge customer-badge">Customer</span>
            <?php elseif ($current_role === 'Restaurant Manager'): ?>
                <span class="brand-badge" style="background:#8b5cf6;">Manager</span>
            <?php elseif ($current_role === 'Rider'): ?>
                <span class="brand-badge" style="background:#f59e0b;">Rider</span>
            <?php endif; ?>
        </a>

        <?php if ($is_logged): ?>
            <ul class="nav-menu">
                <?php if ($current_role === 'Administrator'): ?>
                    <li>
                        <a href="<?php echo $admin_dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                            📊 Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $admin_users_url; ?>" class="nav-link <?php echo ($active_page === 'users') ? 'active' : ''; ?>">
                            👥 Users
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $admin_rest_url; ?>" class="nav-link <?php echo ($active_page === 'restaurants') ? 'active' : ''; ?>">
                            🏪 Restaurants
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $admin_orders_url; ?>" class="nav-link <?php echo ($active_page === 'orders') ? 'active' : ''; ?>">
                            📦 Orders & Deliveries
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                            👤 Profile
                        </a>
                    </li>

                <?php elseif ($current_role === 'Customer'): ?>
                    <li>
                        <a href="<?php echo $cust_dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                            🏠 Home
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $cust_browse_url; ?>" class="nav-link <?php echo ($active_page === 'browse' || $active_page === 'menu') ? 'active' : ''; ?>">
                            🏪 Restaurants
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $cust_fav_url; ?>" class="nav-link <?php echo ($active_page === 'favorites') ? 'active' : ''; ?>">
                            ❤️ Favorites
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $cust_cart_url; ?>" class="nav-link nav-cart-link <?php echo ($active_page === 'cart' || $active_page === 'checkout') ? 'active' : ''; ?>">
                            🛒 Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-pill-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $cust_orders_url; ?>" class="nav-link <?php echo ($active_page === 'orders' || $active_page === 'track') ? 'active' : ''; ?>">
                            📦 My Orders
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $cust_reviews_url; ?>" class="nav-link <?php echo ($active_page === 'reviews') ? 'active' : ''; ?>">
                            ⭐ Reviews
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                            👤 Profile
                        </a>
                    </li>

                <?php elseif ($current_role === 'Restaurant Manager'): ?>
                    <li>
                        <a href="<?php echo $mgr_dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                            📊 Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $mgr_orders_url; ?>" class="nav-link <?php echo ($active_page === 'orders') ? 'active' : ''; ?>">
                            📦 Orders
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $mgr_register_url; ?>" class="nav-link <?php echo ($active_page === 'register_restaurant') ? 'active' : ''; ?>">
                            ➕ New Restaurant
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                            👤 Profile
                        </a>
                    </li>

                <?php elseif ($current_role === 'Rider'): ?>
                    <li>
                        <a href="<?php echo $rider_dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard' || $active_page === 'rider') ? 'active' : ''; ?>">
                            🛵 Rider Portal
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                            👤 Profile
                        </a>
                    </li>

                <?php else: ?>
                    <li>
                        <a href="<?php echo $brand_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                            📊 Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                            👤 Profile
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="nav-user">
                <a href="<?php echo $profile_url; ?>" class="user-pill" style="text-decoration: none; color: inherit;">
                    <span class="user-avatar <?php echo ($current_role === 'Customer') ? 'cust-avatar' : ''; ?>">
                        <?php echo strtoupper(substr($display_username, 0, 1)); ?>
                    </span>
                    <span><?php echo $display_username; ?></span>
                </a>
                <a href="<?php echo $logout_url; ?>" class="btn-logout">Logout</a>
            </div>

        <?php else: ?>
            <div class="nav-user">
                <a href="<?php echo $login_url; ?>" class="btn btn-secondary btn-sm" style="margin-right: 8px;">Sign In</a>
                <a href="<?php echo $register_url; ?>" class="btn btn-primary btn-sm">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
