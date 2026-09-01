<?php
require_once __DIR__ . '/../../controllers/customer/favorites_controller.php';

$pageTitle = 'FoodHub - My Favorite Restaurants';
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

    <div class="page-header">
        <div>
            <h1 class="page-title">❤️ My Favorite Restaurants</h1>
            <p class="page-subtitle">Your saved favorite culinary destinations for fast re-ordering.</p>
        </div>
        <a href="browse_restaurants.php" class="btn btn-secondary btn-sm">Explore More Restaurants</a>
    </div>

    <div class="catalog-grid">
        <?php if (!empty($favorites)): ?>
            <?php foreach ($favorites as $fav): ?>
                <div class="restaurant-card">
                    <div class="restaurant-cover">
                        🍔
                        <!-- Remove Favorite Button -->
                        <form action="actions/remove_favorite.php" method="POST">
                            <input type="hidden" name="restaurant_id" value="<?php echo $fav['restaurant_id']; ?>">
                            <input type="hidden" name="redirect_url" value="favorites.php">
                            <button type="submit" class="fav-action-btn" title="Remove from favorites">❤️</button>
                        </form>
                    </div>
                    <div class="restaurant-body">
                        <h3 class="restaurant-title"><?php echo htmlspecialchars($fav['restaurant_name']); ?></h3>
                        <p class="restaurant-desc"><?php echo htmlspecialchars($fav['description'] ?? 'Delicious food specialties.'); ?></p>
                        
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                            📍 <?php echo htmlspecialchars($fav['address']); ?>
                        </div>

                        <div class="restaurant-meta">
                            <div>
                                <?php if ($fav['total_reviews'] > 0): ?>
                                    <span class="rating-badge">★ <?php echo number_format((float)$fav['avg_rating'], 1); ?> (<?php echo $fav['total_reviews']; ?>)</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">New</span>
                                <?php endif; ?>
                            </div>
                            <div>🍽️ <?php echo $fav['total_items']; ?> menu items</div>
                        </div>

                        <div style="margin-top: 16px;">
                            <a href="view_menu.php?restaurant_id=<?php echo $fav['restaurant_id']; ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
                                View Menu & Order
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">🤍</div>
                <div class="empty-title">No Favorite Restaurants Yet</div>
                <div class="empty-desc">You haven't saved any restaurants to your favorites yet. Click the heart icon on any restaurant to save it here!</div>
                <a href="browse_restaurants.php" class="btn btn-primary">Browse Restaurants</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
