<?php
<<<<<<< HEAD
require_once __DIR__ . '/../../controllers/customer/dashboard_controller.php';

$pageTitle = 'FoodHub - Customer Dashboard';
=======
$pageTitle = 'FoodHub - Customer Dashboard';
$currentPage = 'dashboard';
>>>>>>> origin/admin-module
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
<<<<<<< HEAD
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <span>✅</span>
            <span><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Welcome Hero Banner -->
    <div class="customer-hero">
        <h1>Welcome back, <?php echo htmlspecialchars($customer_name); ?>! 👋</h1>
        <p>Craving something delicious today? Explore top-rated restaurants, order your favorite meals, and track them right to your door.</p>
        
        <form action="browse_restaurants.php" method="GET" class="search-filter-box">
            <input type="text" name="search" placeholder="Search biryani, burgers, pizza, or restaurant..." required>
            <button type="submit" class="btn btn-primary">Find Food</button>
        </form>
    </div>

    <!-- Customer Metrics Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Orders Placed</div>
            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            <div class="stat-desc">Lifetime orders made</div>
=======
    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p class="page-subtitle">Track your food deliveries, view order history, and explore top restaurants</p>
        </div>
        <div>
            <a href="profile.php" class="btn btn-secondary">👤 My Profile</a>
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
        <div class="stat-card" style="--card-accent: #ff4757;">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($customer_stats['total_orders']); ?></div>
            <div class="stat-desc">Lifetime orders placed</div>
>>>>>>> origin/admin-module
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Active Orders</div>
<<<<<<< HEAD
            <div class="stat-value"><?php echo count($active_orders); ?></div>
            <div class="stat-desc">Currently in preparation or delivery</div>
        </div>

        <div class="stat-card" style="--card-accent: #ef4444;">
            <div class="stat-title">Favorite Spots</div>
            <div class="stat-value"><?php echo number_format($total_favorites); ?></div>
            <div class="stat-desc">Saved favorite restaurants</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Reviews Shared</div>
            <div class="stat-value"><?php echo number_format($total_reviews); ?></div>
            <div class="stat-desc">Food reviews submitted</div>
        </div>
    </div>

    <!-- Active Orders Alert Banner (if any) -->
    <?php if (!empty($active_orders)): ?>
    <div class="card" style="border-left: 4px solid var(--primary); margin-bottom: 32px;">
        <div class="card-header">
            <h2 class="card-title">🛵 Live Active Orders</h2>
            <a href="order_history.php" class="btn btn-secondary btn-sm">View All Orders</a>
=======
            <div class="stat-value"><?php echo number_format($customer_stats['active_orders']); ?></div>
            <div class="stat-desc">Currently being prepared / delivered</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Completed Orders</div>
            <div class="stat-value"><?php echo number_format($customer_stats['completed_orders']); ?></div>
            <div class="stat-desc">Successfully delivered</div>
        </div>

        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Spent</div>
            <div class="stat-value">৳<?php echo number_format($customer_stats['total_spent'], 2); ?></div>
            <div class="stat-desc">Across all completed orders</div>
        </div>
    </div>

    <!-- Active Orders Live Tracker Section -->
    <?php 
    $active_orders_list = array_filter($my_orders, function($o) {
        return in_array($o['order_status'], ['Pending', 'Preparing', 'Ready for Delivery', 'Out for Delivery']);
    });
    ?>

    <?php if (!empty($active_orders_list)): ?>
    <div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">🛵 Live Order Tracking</h2>
            <span class="badge badge-rider"><?php echo count($active_orders_list); ?> In Progress</span>
>>>>>>> origin/admin-module
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
<<<<<<< HEAD
                        <th>Status</th>
                        <th>Placed At</th>
                        <th>Track</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_orders as $act_ord): ?>
                    <tr>
                        <td><strong>#<?php echo $act_ord['order_id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($act_ord['restaurant_name']); ?></td>
                        <td><strong>৳<?php echo number_format((float)$act_ord['total_amount'], 2); ?></strong></td>
                        <td>
                            <span class="badge badge-rider"><?php echo htmlspecialchars($act_ord['order_status']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($act_ord['created_at']); ?></td>
                        <td>
                            <a href="order_track.php?order_id=<?php echo $act_ord['order_id']; ?>" class="btn btn-primary btn-sm">
                                Track Live 📍
                            </a>
=======
                        <th>Delivery Address</th>
                        <th>Current Status</th>
                        <th>Assigned Rider</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_orders_list as $act): ?>
                    <tr>
                        <td><strong>#<?php echo $act['order_id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($act['restaurant_name']); ?></strong><br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($act['restaurant_phone']); ?></span>
                        </td>
                        <td><strong>৳<?php echo number_format((float)$act['total_amount'], 2); ?></strong></td>
                        <td style="max-width: 200px; font-size: 0.85rem;"><?php echo htmlspecialchars($act['delivery_address']); ?></td>
                        <td>
                            <?php
                            $st = $act['order_status'];
                            $badge = 'badge-pending';
                            if ($st === 'Out for Delivery') $badge = 'badge-rider';
                            elseif ($st === 'Preparing') $badge = 'badge-manager';
                            ?>
                            <span class="badge <?php echo $badge; ?>" style="font-size: 0.85rem;">⏳ <?php echo htmlspecialchars($st); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($act['rider_name'])): ?>
                                <strong>🛵 <?php echo htmlspecialchars($act['rider_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($act['rider_phone'] ?? ''); ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">Searching for nearby rider...</span>
                            <?php endif; ?>
>>>>>>> origin/admin-module
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

<<<<<<< HEAD
    <!-- Unreviewed Items Reminder Banner -->
    <?php if (!empty($unreviewed_items)): ?>
    <div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 32px; background: #fffbeb;">
        <div class="card-header">
            <h2 class="card-title" style="color: #92400e;">⭐ How was your meal? Leave a Review!</h2>
            <a href="reviews.php" class="btn btn-primary btn-sm">Review Center</a>
        </div>
        <div style="padding: 16px 20px;">
            <p style="color: #78350f; font-size: 0.95rem; margin-bottom: 12px;">
                You recently enjoyed items from delivered orders that haven't been reviewed yet. Help the community by rating them!
            </p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <?php foreach (array_slice($unreviewed_items, 0, 3) as $unrev): ?>
                    <a href="reviews.php#review-<?php echo $unrev['order_id']; ?>-<?php echo $unrev['item_id']; ?>" 
                       class="btn btn-secondary btn-sm" 
                       style="background: #ffffff;">
                        Rate "<?php echo htmlspecialchars($unrev['item_name']); ?>" (<?php echo htmlspecialchars($unrev['restaurant_name']); ?>)
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Featured Restaurants Section -->
    <div class="page-header" style="margin-top: 16px; margin-bottom: 16px;">
        <div>
            <h2 class="page-title" style="font-size: 1.4rem;">Top Restaurants Near You</h2>
            <p class="page-subtitle">Hand-picked approved culinary partners</p>
        </div>
        <a href="browse_restaurants.php" class="btn btn-secondary btn-sm">Explore All Restaurants →</a>
    </div>

    <div class="catalog-grid">
        <?php if (!empty($featured_restaurants)): ?>
            <?php foreach ($featured_restaurants as $rest): ?>
                <div class="restaurant-card">
                    <div class="restaurant-cover">
                        🍔
                        <!-- Favorite Button -->
                        <?php if ($rest['is_favorite'] > 0): ?>
                            <form action="actions/remove_favorite.php" method="POST">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rest['restaurant_id']; ?>">
                                <input type="hidden" name="redirect_url" value="dashboard.php">
                                <button type="submit" class="fav-action-btn" title="Remove from favorites">❤️</button>
                            </form>
                        <?php else: ?>
                            <form action="actions/add_favorite.php" method="POST">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rest['restaurant_id']; ?>">
                                <input type="hidden" name="redirect_url" value="dashboard.php">
                                <button type="submit" class="fav-action-btn" title="Add to favorites">🤍</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="restaurant-body">
                        <h3 class="restaurant-title"><?php echo htmlspecialchars($rest['restaurant_name']); ?></h3>
                        <p class="restaurant-desc"><?php echo htmlspecialchars($rest['description'] ?? 'Authentic flavors and fresh meals.'); ?></p>
                        
                        <div class="restaurant-meta">
                            <div>
                                <?php if ($rest['total_reviews'] > 0): ?>
                                    <span class="rating-badge">★ <?php echo number_format((float)$rest['avg_rating'], 1); ?> (<?php echo $rest['total_reviews']; ?>)</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">New</span>
                                <?php endif; ?>
                            </div>
                            <div>🍽️ <?php echo $rest['total_items']; ?> items</div>
                        </div>

                        <div style="margin-top: 16px;">
                            <a href="view_menu.php?restaurant_id=<?php echo $rest['restaurant_id']; ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
                                View Menu & Order
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">🏪</div>
                <div class="empty-title">No Restaurants Available</div>
                <div class="empty-desc">There are no approved restaurants online right now. Check back soon!</div>
            </div>
        <?php endif; ?>
=======
    <!-- Partner Restaurants Grid -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">🏪 Explore Partner Restaurants</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php if (!empty($partner_restaurants)): ?>
                    <?php foreach ($partner_restaurants as $rest): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; background: #fafafa; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                                🍽️ <?php echo htmlspecialchars($rest['restaurant_name']); ?>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px; line-height: 1.4;">
                                <?php echo htmlspecialchars($rest['description'] ?? 'Delicious food crafted with love.'); ?>
                            </p>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                📍 <?php echo htmlspecialchars($rest['address']); ?>
                            </div>
                        </div>
                        <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                            <span class="badge badge-active"><?php echo $rest['available_items']; ?> Menu Items</span>
                            <span style="font-size: 0.82rem; color: var(--primary); font-weight: 600;">📞 <?php echo htmlspecialchars($rest['phone']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No restaurants available at this moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order History Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📜 My Order History</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_orders)): ?>
                        <?php foreach ($my_orders as $ord): ?>
                        <tr>
                            <td><strong>#<?php echo $ord['order_id']; ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($ord['restaurant_name']); ?></strong></td>
                            <td><strong>৳<?php echo number_format((float)$ord['total_amount'], 2); ?></strong></td>
                            <td>
                                <span style="font-size: 0.85rem;"><?php echo htmlspecialchars($ord['payment_method']); ?></span><br>
                                <span class="badge <?php echo ($ord['payment_status'] === 'Paid') ? 'badge-delivered' : 'badge-pending'; ?>" style="font-size: 0.72rem;">
                                    <?php echo htmlspecialchars($ord['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $st = $ord['order_status'];
                                $badgeClass = 'badge-pending';
                                if ($st === 'Delivered') $badgeClass = 'badge-delivered';
                                elseif ($st === 'Cancelled') $badgeClass = 'badge-cancelled';
                                elseif ($st === 'Preparing' || $st === 'Out for Delivery' || $st === 'Ready for Delivery') $badgeClass = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st); ?></span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);"><?php echo htmlspecialchars($ord['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                You haven't placed any orders yet. Explore our restaurants above!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
>>>>>>> origin/admin-module
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
