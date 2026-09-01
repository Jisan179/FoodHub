<?php
require_once __DIR__ . '/../../controllers/customer/dashboard_controller.php';

$pageTitle = 'FoodHub - Customer Dashboard';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
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
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Active Orders</div>
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
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
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
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
