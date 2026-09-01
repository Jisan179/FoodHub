<?php
require_once __DIR__ . '/../../controllers/customer/browse_controller.php';

$pageTitle = 'FoodHub - Browse Restaurants & Food';
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

    <!-- Search Header -->
    <div class="page-header" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title">Explore Restaurants & Food</h1>
            <p class="page-subtitle">Discover top eateries, freshly prepared meals, and fast delivery in your area.</p>
        </div>
        <form action="browse_restaurants.php" method="GET" style="display: flex; gap: 8px; min-width: 320px;">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search food or restaurant..." 
                value="<?php echo htmlspecialchars($search_query); ?>"
            >
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search_query)): ?>
                <a href="browse_restaurants.php" class="btn btn-secondary" title="Clear Search">✕</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Food Item Search Matches (if search query entered) -->
    <?php if (!empty($search_query) && !empty($food_search_results)): ?>
    <div style="margin-bottom: 36px;">
        <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px;">
            🍲 Food Items matching "<em><?php echo htmlspecialchars($search_query); ?></em>" (<?php echo count($food_search_results); ?>)
        </h2>
        <div class="menu-grid">
            <?php foreach ($food_search_results as $f_item): ?>
            <div class="food-card">
                <div>
                    <div class="food-card-header">
                        <span class="food-tag"><?php echo htmlspecialchars($f_item['category']); ?></span>
                        <h3 class="food-title"><?php echo htmlspecialchars($f_item['item_name']); ?></h3>
                        <div style="font-size: 0.82rem; color: var(--primary); font-weight: 600; margin-bottom: 4px;">
                            🏪 <?php echo htmlspecialchars($f_item['restaurant_name']); ?>
                        </div>
                    </div>
                    <p class="food-desc"><?php echo htmlspecialchars($f_item['item_description'] ?? ''); ?></p>
                </div>
                <div class="food-card-footer">
                    <div class="food-price">৳<?php echo number_format((float)$f_item['price'], 2); ?></div>
                    <form action="actions/add_to_cart.php" method="POST" style="display: inline;">
                        <input type="hidden" name="item_id" value="<?php echo $f_item['item_id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        <button type="submit" class="btn btn-primary btn-sm">+ Add to Cart</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Restaurants List Grid -->
    <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px;">
        <?php echo !empty($search_query) ? 'Matching Partner Restaurants' : 'All Partner Restaurants'; ?> 
        (<?php echo count($restaurants); ?>)
    </h2>

    <div class="catalog-grid">
        <?php if (!empty($restaurants)): ?>
            <?php foreach ($restaurants as $rest): ?>
                <div class="restaurant-card">
                    <div class="restaurant-cover">
                        🍔
                        <!-- Favorite Button -->
                        <?php if ($rest['is_favorite'] > 0): ?>
                            <form action="actions/remove_favorite.php" method="POST">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rest['restaurant_id']; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <button type="submit" class="fav-action-btn" title="Remove from favorites">❤️</button>
                            </form>
                        <?php else: ?>
                            <form action="actions/add_favorite.php" method="POST">
                                <input type="hidden" name="restaurant_id" value="<?php echo $rest['restaurant_id']; ?>">
                                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <button type="submit" class="fav-action-btn" title="Add to favorites">🤍</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="restaurant-body">
                        <h3 class="restaurant-title"><?php echo htmlspecialchars($rest['restaurant_name']); ?></h3>
                        <p class="restaurant-desc"><?php echo htmlspecialchars($rest['description'] ?? 'Delicious specialties prepared fresh.'); ?></p>
                        
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            📍 <?php echo htmlspecialchars($rest['address']); ?>
                        </div>

                        <div class="restaurant-meta">
                            <div>
                                <?php if ($rest['total_reviews'] > 0): ?>
                                    <span class="rating-badge">★ <?php echo number_format((float)$rest['avg_rating'], 1); ?> (<?php echo $rest['total_reviews']; ?>)</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">New</span>
                                <?php endif; ?>
                            </div>
                            <div>🍽️ <?php echo $rest['total_items']; ?> items available</div>
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
                <div class="empty-icon">🔍</div>
                <div class="empty-title">No Restaurants Found</div>
                <div class="empty-desc">We couldn't find any approved restaurants matching your criteria. Try another search term!</div>
                <a href="browse_restaurants.php" class="btn btn-secondary">View All Restaurants</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
