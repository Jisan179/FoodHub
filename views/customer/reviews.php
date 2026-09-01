<?php
require_once __DIR__ . '/../../controllers/customer/reviews_controller.php';

$pageTitle = 'FoodHub - Food Ratings & Reviews';
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
            <h1 class="page-title">⭐ Food Reviews & Ratings</h1>
            <p class="page-subtitle">Share your dining feedback and review dishes from completed deliveries.</p>
        </div>
    </div>

    <!-- Section 1: Items Waiting for Review from Delivered Orders -->
    <?php if (!empty($pending_review_items)): ?>
    <div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 32px;">
        <div class="card-header">
            <h2 class="card-title" style="color: #92400e;">📝 Delivered Meals Waiting for Your Feedback (<?php echo count($pending_review_items); ?>)</h2>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
                <?php foreach ($pending_review_items as $unrev): ?>
                <div id="review-<?php echo $unrev['order_id']; ?>-<?php echo $unrev['item_id']; ?>" 
                     style="background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-sm); padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div>
                            <strong style="font-size: 1.05rem;"><?php echo htmlspecialchars($unrev['item_name']); ?></strong>
                            <div style="font-size: 0.82rem; color: #92400e;">
                                🏪 <?php echo htmlspecialchars($unrev['restaurant_name']); ?> • Order #<?php echo $unrev['order_id']; ?>
                            </div>
                        </div>
                        <span class="badge badge-delivered">Delivered</span>
                    </div>

                    <form action="actions/submit_review.php" method="POST" style="margin-top: 12px;">
                        <input type="hidden" name="order_id" value="<?php echo $unrev['order_id']; ?>">
                        <input type="hidden" name="item_id" value="<?php echo $unrev['item_id']; ?>">
                        <input type="hidden" name="redirect_url" value="reviews.php">

                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label" style="font-size: 0.85rem;">Rating:</label>
                            <select name="rating" class="form-control" style="font-size: 0.88rem;" required>
                                <option value="5">⭐⭐⭐⭐⭐ (5 - Exceptional)</option>
                                <option value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
                                <option value="3">⭐⭐⭐ (3 - Average / Okay)</option>
                                <option value="2">⭐⭐ (2 - Below Average)</option>
                                <option value="1">⭐ (1 - Poor)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label class="form-label" style="font-size: 0.85rem;">Comment / Feedback:</label>
                            <textarea 
                                name="comment" 
                                class="form-control" 
                                rows="2" 
                                placeholder="How was the taste, freshness, and packaging?" 
                                style="font-size: 0.88rem;" 
                                required
                            ></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                            Submit Food Review
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 2: Submitted Reviews -->
    <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px;">
        My Submitted Reviews (<?php echo count($my_reviews); ?>)
    </h2>

    <?php if (!empty($my_reviews)): ?>
        <?php foreach ($my_reviews as $rev): ?>
        <div class="review-card">
            <div class="review-card-header">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 2px;">
                        <?php echo htmlspecialchars($rev['item_name']); ?>
                    </h3>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        🏪 <?php echo htmlspecialchars($rev['restaurant_name']); ?> • Order #<?php echo $rev['order_id']; ?> • <?php echo htmlspecialchars($rev['created_at']); ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="star-rating">
                        <?php 
                        $stars = intval($rev['rating']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $stars) ? '★' : '☆';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.5; margin-bottom: 14px; background: #f8fafc; padding: 12px 16px; border-radius: var(--radius-sm);">
                "<?php echo htmlspecialchars($rev['comment']); ?>"
            </p>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button 
                    type="button" 
                    class="btn btn-secondary btn-sm" 
                    onclick="document.getElementById('edit-review-<?php echo $rev['review_id']; ?>').style.display = (document.getElementById('edit-review-<?php echo $rev['review_id']; ?>').style.display === 'none' ? 'block' : 'none');"
                >
                    ✏️ Edit
                </button>
                <form action="actions/delete_review.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');" style="display: inline;">
                    <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
                </form>
            </div>

            <!-- Edit Inline Form -->
            <div id="edit-review-<?php echo $rev['review_id']; ?>" style="display: none; margin-top: 14px; padding: 16px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: #ffffff;">
                <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 10px;">Edit Your Review</h4>
                <form action="actions/edit_review.php" method="POST">
                    <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                    
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label" style="font-size: 0.85rem;">Rating:</label>
                        <select name="rating" class="form-control" style="font-size: 0.88rem;">
                            <option value="5" <?php echo ($rev['rating'] == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 - Exceptional)</option>
                            <option value="4" <?php echo ($rev['rating'] == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 - Very Good)</option>
                            <option value="3" <?php echo ($rev['rating'] == 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3 - Average / Okay)</option>
                            <option value="2" <?php echo ($rev['rating'] == 2) ? 'selected' : ''; ?>>⭐⭐ (2 - Below Average)</option>
                            <option value="1" <?php echo ($rev['rating'] == 1) ? 'selected' : ''; ?>>⭐ (1 - Poor)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Comment:</label>
                        <textarea name="comment" class="form-control" rows="2" style="font-size: 0.88rem;" required><?php echo htmlspecialchars($rev['comment']); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('edit-review-<?php echo $rev['review_id']; ?>').style.display = 'none';">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">⭐</div>
            <div class="empty-title">No Reviews Submitted Yet</div>
            <div class="empty-desc">Once you complete an order and receive your delivery, you will be able to review the delicious meals you enjoyed!</div>
            <a href="order_history.php" class="btn btn-secondary">View Order History</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
