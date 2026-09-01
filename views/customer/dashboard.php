<?php
if (!isset($customer_stats)) {
    require_once __DIR__ . '/../../controllers/customer/dashboard_controller.php';
}

$pageTitle = 'FoodHub - Customer Dashboard';
$currentPage = 'dashboard';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$is_in_customer_dir = (strpos($_SERVER['PHP_SELF'], '/customer/') !== false);
$cust_prefix = $is_in_customer_dir ? '' : 'customer/';
$profile_link = $is_in_customer_dir ? '../profile.php' : 'profile.php';
$dash_redirect = $is_in_customer_dir ? '../dashboard.php' : '../../dashboard.php';
$display_name = $customer_name ?? $user_name ?? 'Foodie';
?>

<div class="main-wrapper">
    <!-- Flash / Alert Messages -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <span>✅</span>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Welcome Hero Banner -->
    <div class="customer-hero">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 8px;">Welcome back, <?php echo htmlspecialchars($display_name); ?>! 👋</h1>
                <p style="color: #cbd5e1; font-size: 1.05rem; max-width: 650px; margin-bottom: 20px;">
                    Craving something delicious today? Explore top-rated partner restaurants, order your favorite meals, and track your food in real-time.
                </p>
            </div>
            <div>
                <a href="<?php echo $profile_link; ?>" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.25);">
                    👤 My Profile
                </a>
            </div>
        </div>

        <form action="<?php echo $cust_prefix; ?>browse_restaurants.php" method="GET" class="search-filter-box">
            <input type="text" name="search" placeholder="Search biryani, burgers, pizza, or restaurant..." required>
            <button type="submit" class="btn btn-primary">Find Food</button>
        </form>
    </div>

    <!-- Customer Summary Metrics Grid -->
    <div class="stats-grid">
        <div class="stat-card" style="--card-accent: #ff4757;">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($customer_stats['total_orders'] ?? $total_orders ?? 0); ?></div>
            <div class="stat-desc">Lifetime orders placed</div>
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Active Orders</div>
            <div class="stat-value"><?php echo count($active_orders ?? []); ?></div>
            <div class="stat-desc">In preparation or out for delivery</div>
        </div>

        <div class="stat-card" style="--card-accent: #ef4444;">
            <div class="stat-title">Favorite Spots</div>
            <div class="stat-value"><?php echo number_format($total_favorites ?? 0); ?></div>
            <div class="stat-desc">Saved favorite restaurants</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Reviews Shared</div>
            <div class="stat-value"><?php echo number_format($total_reviews ?? 0); ?></div>
            <div class="stat-desc">Food ratings submitted</div>
        </div>

        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Spent</div>
            <div class="stat-value">৳<?php echo number_format((float)($customer_stats['total_spent'] ?? 0.0), 2); ?></div>
            <div class="stat-desc">Across completed orders</div>
        </div>
    </div>

    <!-- Live Active Orders Tracker Section -->
    <?php if (!empty($active_orders)): ?>
    <div class="card" style="border-left: 4px solid var(--primary); margin-bottom: 32px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2 class="card-title" style="margin: 0;">🛵 Live Active Orders</h2>
                <span class="badge badge-rider"><?php echo count($active_orders); ?> In Progress</span>
            </div>
            <a href="<?php echo $cust_prefix; ?>order_history.php" class="btn btn-secondary btn-sm">View All Orders</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Delivery Address</th>
                        <th>Current Status</th>
                        <th>Assigned Rider</th>
                        <th>Track</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_orders as $act): ?>
                    <tr>
                        <td><strong>#<?php echo $act['order_id']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($act['restaurant_name']); ?></strong>
                            <?php if (!empty($act['restaurant_phone'])): ?>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($act['restaurant_phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><strong>৳<?php echo number_format((float)$act['total_amount'], 2); ?></strong></td>
                        <td style="max-width: 220px; font-size: 0.85rem;"><?php echo htmlspecialchars($act['delivery_address']); ?></td>
                        <td>
                            <?php
                            $st = $act['order_status'];
                            $badge = 'badge-pending';
                            if ($st === 'Out for Delivery') $badge = 'badge-rider';
                            elseif ($st === 'Preparing') $badge = 'badge-manager';
                            elseif ($st === 'Ready for Delivery') $badge = 'badge-active';
                            ?>
                            <span class="badge <?php echo $badge; ?>" style="font-size: 0.85rem;">⏳ <?php echo htmlspecialchars($st); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($act['rider_name']) && $act['rider_name'] !== 'Unassigned'): ?>
                                <strong>🛵 <?php echo htmlspecialchars($act['rider_name']); ?></strong>
                                <?php if (!empty($act['rider_phone'])): ?>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($act['rider_phone']); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">Searching for nearby rider...</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $cust_prefix; ?>order_track.php?order_id=<?php echo $act['order_id']; ?>" class="btn btn-primary btn-sm">
                                Track Live 📍
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Unreviewed Items Reminder Banner -->
    <?php if (!empty($unreviewed_items)): ?>
    <div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 32px; background: #fffbeb;">
        <div class="card-header" style="background: transparent; border-bottom: 1px solid #fde68a;">
            <h2 class="card-title" style="color: #92400e; margin: 0;">⭐ How was your meal? Leave a Review!</h2>
            <a href="<?php echo $cust_prefix; ?>reviews.php" class="btn btn-primary btn-sm">Review Center</a>
        </div>
        <div style="padding: 16px 20px;">
            <p style="color: #78350f; font-size: 0.95rem; margin-bottom: 12px;">
                You recently enjoyed items from delivered orders that haven't been reviewed yet. Help the FoodHub community by rating them!
            </p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <?php foreach (array_slice($unreviewed_items, 0, 3) as $unrev): ?>
                    <a href="<?php echo $cust_prefix; ?>reviews.php#review-<?php echo $unrev['order_id']; ?>-<?php echo $unrev['item_id']; ?>" 
                       class="btn btn-secondary btn-sm" 
                       style="background: #ffffff;">
                        Rate "<?php echo htmlspecialchars($unrev['item_name']); ?>" (<?php echo htmlspecialchars($unrev['restaurant_name']); ?>)
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top Restaurants Near You -->
    <div class="page-header" style="margin-top: 16px; margin-bottom: 16px;">
        <div>
            <h2 class="page-title" style="font-size: 1.4rem;">Top Restaurants Near You</h2>
            <p class="page-subtitle">Hand-picked approved culinary partners in your neighborhood</p>
        </div>
        <a href="<?php echo $cust_prefix; ?>browse_restaurants.php" class="btn btn-secondary btn-sm">Explore All Restaurants →</a>
    </div>

    <div class="catalog-grid">
        <?php if (!empty($featured_restaurants)): ?>
            <?php foreach ($featured_restaurants as $rest): ?>
                <div class="restaurant-card">
                    <div class="restaurant-cover">
                        🍔
                        <!-- Favorite Toggle -->
                        <?php if (!empty($rest['is_favorite'])): ?>
                            <form action="<?php echo $cust_prefix; ?>actions/remove_favorite.php" method="POST">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rest['restaurant_id']; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo $dash_redirect; ?>">
                                <button type="submit" class="fav-action-btn" title="Remove from favorites">❤️</button>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo $cust_prefix; ?>actions/add_favorite.php" method="POST">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rest['restaurant_id']; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo $dash_redirect; ?>">
                                <button type="submit" class="fav-action-btn" title="Add to favorites">🤍</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="restaurant-body">
                        <h3 class="restaurant-title"><?php echo htmlspecialchars($rest['restaurant_name']); ?></h3>
                        <p class="restaurant-desc"><?php echo htmlspecialchars($rest['description'] ?? 'Authentic flavors and freshly prepared dishes.'); ?></p>
                        
                        <div class="restaurant-meta">
                            <div>
                                <?php if (!empty($rest['total_reviews']) && $rest['total_reviews'] > 0): ?>
                                    <span class="rating-badge">★ <?php echo number_format((float)$rest['avg_rating'], 1); ?> (<?php echo $rest['total_reviews']; ?>)</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">New</span>
                                <?php endif; ?>
                            </div>
                            <div>🍽️ <?php echo $rest['total_items'] ?? $rest['available_items'] ?? 0; ?> items</div>
                        </div>

                        <div style="margin-top: 16px;">
                            <a href="<?php echo $cust_prefix; ?>view_menu.php?restaurant_id=<?php echo $rest['restaurant_id']; ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
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
    </div>

    <!-- Recent Order History Section -->
    <div class="card" style="margin-top: 32px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title" style="margin: 0;">📜 Recent Order History</h2>
            <a href="<?php echo $cust_prefix; ?>order_history.php" class="btn btn-secondary btn-sm">Full History</a>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_orders)): ?>
                        <?php foreach (array_slice($my_orders, 0, 5) as $ord): ?>
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
                            <td>
                                <a href="<?php echo $cust_prefix; ?>order_track.php?order_id=<?php echo $ord['order_id']; ?>" class="btn btn-secondary btn-sm">
                                    Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                You haven't placed any orders yet. Explore our top restaurants above to get started!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
