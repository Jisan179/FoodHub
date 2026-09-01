<?php
require_once __DIR__ . '/../../controllers/customer/menu_controller.php';

$pageTitle = 'FoodHub - ' . htmlspecialchars($restaurant['restaurant_name']) . ' Menu';
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

    <!-- Cart Conflict Notification Modal / Banner -->
    <?php if (isset($_SESSION['cart_conflict'])): 
        $conflict = $_SESSION['cart_conflict'];
        unset($_SESSION['cart_conflict']);
    ?>
    <div class="card" style="border-left: 4px solid var(--warning); background: var(--warning-bg); margin-bottom: 24px;">
        <div style="padding: 20px;">
            <h3 style="color: #92400e; margin-bottom: 8px;">⚠️ Replace Items in Cart?</h3>
            <p style="color: #78350f; font-size: 0.95rem; margin-bottom: 16px;">
                Your cart currently contains items from <strong><?php echo htmlspecialchars($conflict['cart_restaurant_name']); ?></strong>. 
                FoodHub allows ordering from one restaurant at a time. Would you like to clear your existing cart and add items from <strong><?php echo htmlspecialchars($conflict['new_restaurant_name']); ?></strong>?
            </p>
            <div style="display: flex; gap: 12px;">
                <form action="actions/add_to_cart.php" method="POST">
                    <input type="hidden" name="item_id" value="<?php echo $conflict['item_id']; ?>">
                    <input type="hidden" name="quantity" value="<?php echo $conflict['quantity']; ?>">
                    <input type="hidden" name="clear_if_conflict" value="1">
                    <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Yes, Clear Cart & Add Item</button>
                </form>
                <a href="cart.php" class="btn btn-secondary btn-sm">View Current Cart</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Restaurant Header Card -->
    <div class="card" style="margin-bottom: 28px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <a href="browse_restaurants.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; display: inline-block; margin-bottom: 8px;">
                    ← Back to Restaurants
                </a>
                <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">
                    <?php echo htmlspecialchars($restaurant['restaurant_name']); ?>
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 12px; max-width: 650px;">
                    <?php echo htmlspecialchars($restaurant['description'] ?? 'Delicious specialties prepared fresh daily.'); ?>
                </p>
                <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap; font-size: 0.9rem; color: var(--text-muted);">
                    <div>📍 <?php echo htmlspecialchars($restaurant['address']); ?></div>
                    <div>📞 <?php echo htmlspecialchars($restaurant['phone']); ?></div>
                    <div>
                        <?php if ($restaurant['total_reviews'] > 0): ?>
                            <span class="rating-badge">★ <?php echo number_format((float)$restaurant['avg_rating'], 1); ?> (<?php echo $restaurant['total_reviews']; ?> reviews)</span>
                        <?php else: ?>
                            <span class="badge" style="background:#f1f5f9; color:#64748b;">No reviews yet</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Favorite / Unfavorite Trigger -->
            <div>
                <?php if ($is_favorited): ?>
                    <form action="actions/remove_favorite.php" method="POST">
                        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['restaurant_id']; ?>">
                        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        <button type="submit" class="btn btn-secondary" style="border-color: #fecaca; color: #ef4444;">
                            ❤️ In Favorites
                        </button>
                    </form>
                <?php else: ?>
                    <form action="actions/add_favorite.php" method="POST">
                        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['restaurant_id']; ?>">
                        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        <button type="submit" class="btn btn-secondary">
                            🤍 Add to Favorites
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <?php if (!empty($categories)): ?>
    <div class="category-filter-bar">
        <a href="view_menu.php?restaurant_id=<?php echo $restaurant_id; ?>&category=All" 
           class="category-pill <?php echo ($category === 'All') ? 'active' : ''; ?>">
            All Items
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="view_menu.php?restaurant_id=<?php echo $restaurant_id; ?>&category=<?php echo urlencode($cat); ?>" 
               class="category-pill <?php echo ($category === $cat) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Menu Items Grid -->
    <div class="menu-grid">
        <?php if (!empty($menu_items)): ?>
            <?php foreach ($menu_items as $item): ?>
            <div class="food-card">
                <div>
                    <div class="food-card-header">
                        <span class="food-tag"><?php echo htmlspecialchars($item['category']); ?></span>
                        <h3 class="food-title"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        <?php if ($item['review_count'] > 0): ?>
                            <div style="font-size: 0.82rem; color: #d97706; font-weight: 700; margin-bottom: 6px;">
                                ★ <?php echo number_format((float)$item['avg_rating'], 1); ?> (<?php echo $item['review_count']; ?>)
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="food-desc"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                </div>
                
                <div class="food-card-footer">
                    <div class="food-price">৳<?php echo number_format((float)$item['price'], 2); ?></div>
                    <form action="actions/add_to_cart.php" method="POST" style="display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        
                        <select name="quantity" class="form-control" style="width: 60px; padding: 6px 8px; font-size: 0.88rem;">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary btn-sm">+ Add</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">🍽️</div>
                <div class="empty-title">No Menu Items Found</div>
                <div class="empty-desc">There are no available items listed under this category right now.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
