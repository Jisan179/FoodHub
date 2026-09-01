<?php
// Shared Navigation Bar Component (Role-Aware for Admin & Customer)
$active_page = $currentPage ?? 'dashboard';
$current_role = $_SESSION['role'] ?? 'Guest';
$display_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';

$in_admin_folder    = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$in_customer_folder = (strpos($_SERVER['PHP_SELF'], '/customer/') !== false);
$in_views_folder    = (strpos($_SERVER['PHP_SELF'], '/views/') !== false);

if ($in_admin_folder) {
    $dash_url       = 'dashboard.php';
    $users_url      = 'users.php';
    $rest_url       = 'restaurants.php';
    $orders_url     = 'orders.php';
    $logout_url     = '../logout.php';
} elseif ($in_customer_folder) {
    $dash_url       = 'dashboard.php';
    $browse_url     = 'browse_restaurants.php';
    $fav_url        = 'favorites.php';
    $cart_url       = 'cart.php';
    $orders_url     = 'order_history.php';
    $reviews_url    = 'reviews.php';
    $logout_url     = '../logout.php';
} elseif ($in_views_folder) {
    $dash_url       = 'dashboard.php';
    $users_url      = 'users.php';
    $rest_url       = 'restaurants.php';
    $orders_url     = 'orders.php';
    $browse_url     = 'browse_restaurants.php';
    $fav_url        = 'favorites.php';
    $cart_url       = 'cart.php';
    $reviews_url    = 'reviews.php';
    $logout_url     = '../../logout.php';
} else {
    $dash_url       = ($current_role === 'Admin') ? 'admin/dashboard.php' : 'customer/dashboard.php';
    $users_url      = 'admin/users.php';
    $rest_url       = 'admin/restaurants.php';
    $orders_url     = ($current_role === 'Admin') ? 'admin/orders.php' : 'customer/order_history.php';
    $browse_url     = 'customer/browse_restaurants.php';
    $fav_url        = 'customer/favorites.php';
    $cart_url       = 'customer/cart.php';
    $reviews_url    = 'customer/reviews.php';
    $logout_url     = 'logout.php';
}

// Fetch live cart count if logged in as customer
$cart_count = 0;
if ($current_role === 'Customer' && isset($conn) && isset($_SESSION['user_id'])) {
    if (function_exists('count_cart_items')) {
        $cart_count = count_cart_items($conn, $_SESSION['user_id']);
    }
}
?>
<nav class="admin-navbar <?php echo ($current_role === 'Customer') ? 'customer-navbar' : ''; ?>">
    <div class="nav-container">
        <a href="<?php echo $dash_url; ?>" class="brand-logo">
            <span>🍔 FoodHub</span>
            <span class="brand-badge <?php echo ($current_role === 'Customer') ? 'customer-badge' : ''; ?>">
                <?php echo ($current_role === 'Admin') ? 'Admin Portal' : 'Customer'; ?>
            </span>
        </a>

        <?php if ($current_role === 'Admin'): ?>
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
                <span class="user-avatar <?php echo ($current_role === 'Customer') ? 'cust-avatar' : ''; ?>">
                    <?php echo strtoupper(substr($display_username, 0, 1)); ?>
                </span>
                <span><?php echo $display_username; ?></span>
            </div>
            <a href="<?php echo $logout_url; ?>" class="btn-logout">Logout</a>
        </div>
    </div>
</nav>
