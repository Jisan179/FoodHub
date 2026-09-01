<?php
// Shared Role-Aware Navigation Bar Component
$active_page = $currentPage ?? 'dashboard';

$user_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_display_name = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'User');
$user_role = isset($_SESSION['role']) ? normalize_role($_SESSION['role']) : 'Customer';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$in_views_folder = (strpos($_SERVER['PHP_SELF'], '/views/') !== false);

if ($in_admin_folder) {
    $dash_url     = 'dashboard.php';
    $users_url    = 'users.php';
    $rest_url     = 'restaurants.php';
    $orders_url   = 'orders.php';
    $profile_url  = '../profile.php';
    $pwd_url      = '../change-password.php';
    $logout_url   = '../logout.php';
    $brand_url    = 'dashboard.php';
} elseif ($in_views_folder) {
    $dash_url     = ($user_role === 'Administrator') ? 'admin/dashboard.php' : 'dashboard.php';
    $users_url    = 'admin/users.php';
    $rest_url     = 'admin/restaurants.php';
    $orders_url   = 'admin/orders.php';
    $profile_url  = 'profile.php';
    $pwd_url      = 'change-password.php';
    $logout_url   = 'logout.php';
    $brand_url    = ($user_role === 'Administrator') ? 'admin/dashboard.php' : 'dashboard.php';
} else {
    $dash_url     = ($user_role === 'Administrator') ? 'admin/dashboard.php' : 'dashboard.php';
    $users_url    = 'admin/users.php';
    $rest_url     = 'admin/restaurants.php';
    $orders_url   = 'admin/orders.php';
    $profile_url  = 'profile.php';
    $pwd_url      = 'change-password.php';
    $logout_url   = 'logout.php';
    $brand_url    = ($user_role === 'Administrator') ? 'admin/dashboard.php' : 'dashboard.php';
}

$badge_class = 'badge-customer';
if ($user_role === 'Administrator') $badge_class = 'badge-admin';
elseif ($user_role === 'Restaurant Manager') $badge_class = 'badge-manager';
elseif ($user_role === 'Rider') $badge_class = 'badge-rider';
?>

<nav class="admin-navbar">
    <div class="nav-container">
        <a href="<?php echo $brand_url; ?>" class="brand-logo">
            <span>🍔 FoodHub</span>
            <span class="brand-badge"><?php echo htmlspecialchars($user_role); ?></span>
        </a>

        <?php if ($user_logged_in): ?>
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

        <div class="nav-user">
            <div class="user-pill">
                <span class="user-avatar"><?php echo strtoupper(substr($user_display_name, 0, 1)); ?></span>
                <span><?php echo $user_display_name; ?></span>
                <span class="badge <?php echo $badge_class; ?>" style="font-size: 0.72rem; padding: 2px 6px;">
                    <?php echo htmlspecialchars($user_role); ?>
                </span>
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
