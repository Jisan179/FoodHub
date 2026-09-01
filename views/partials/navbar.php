<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$active_page = $currentPage ?? 'dashboard';
$is_logged = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$raw_role = $_SESSION['role'] ?? 'Guest';

if (strcasecmp($raw_role, 'Admin') === 0 || strcasecmp($raw_role, 'Administrator') === 0) {
    $current_role = 'Administrator';
} else {
    $current_role = $raw_role;
}

$display_username = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User');

$script_self = $_SERVER['PHP_SELF'] ?? '';
$in_actions_folder = (strpos($script_self, '/actions/') !== false);
$in_admin_folder = (strpos($script_self, '/admin/') !== false);
$in_customer_folder = (strpos($script_self, '/customer/') !== false);
$in_views_folder = (strpos($script_self, '/views/') !== false);

if ($in_actions_folder) {
    $root_url = '../../';
} elseif ($in_admin_folder || $in_customer_folder || $in_views_folder) {
    $root_url = '../';
} else {
    $root_url = '';
}

// Navigation URLs
$profile_url  = $root_url . 'profile.php';
$logout_url   = $root_url . 'logout.php';
$login_url    = $root_url . 'login.php';
$register_url = $root_url . 'register.php';

// Role-specific Dash URL & Brand URL
if ($current_role === 'Administrator') {
    $dash_url = $in_admin_folder ? 'dashboard.php' : ($in_customer_folder ? '../admin/dashboard.php' : 'admin/dashboard.php');
} else {
    $dash_url = $root_url . 'dashboard.php';
}
$brand_url = $dash_url;

// Admin URLs
$users_url        = $in_admin_folder ? 'users.php' : ($in_customer_folder ? '../admin/users.php' : 'admin/users.php');
$admin_rest_url   = $in_admin_folder ? 'restaurants.php' : ($in_customer_folder ? '../admin/restaurants.php' : 'admin/restaurants.php');
$admin_orders_url = $in_admin_folder ? 'orders.php' : ($in_customer_folder ? '../admin/orders.php' : 'admin/orders.php');

// Customer URLs
$browse_url      = $in_customer_folder ? 'browse_restaurants.php' : $root_url . 'customer/browse_restaurants.php';
$fav_url         = $in_customer_folder ? 'favorites.php' : $root_url . 'customer/favorites.php';
$cart_url        = $in_customer_folder ? 'cart.php' : $root_url . 'customer/cart.php';
$cust_orders_url = $in_customer_folder ? 'order_history.php' : $root_url . 'customer/order_history.php';
$reviews_url     = $in_customer_folder ? 'reviews.php' : $root_url . 'customer/reviews.php';

// Calculate Cart Items for Customer
$cart_count = 0;
if ($is_logged && $current_role === 'Customer') {
    if (function_exists('count_cart_items') && isset($conn) && $conn) {
        $cart_count = count_cart_items($conn, intval($_SESSION['user_id']));
    } elseif (isset($conn) && $conn) {
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
                        <a href="<?php echo $cust_orders_url; ?>" class="nav-link <?php echo ($active_page === 'orders' || $active_page === 'track') ? 'active' : ''; ?>">
                            📦 My Orders
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $reviews_url; ?>" class="nav-link <?php echo ($active_page === 'reviews') ? 'active' : ''; ?>">
                            ⭐ Reviews
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $profile_url; ?>" class="nav-link <?php echo ($active_page === 'profile') ? 'active' : ''; ?>">
                            👤 Profile
                        </a>
                    </li>

                <?php else: ?>
                    <li>
                        <a href="<?php echo $dash_url; ?>" class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
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
