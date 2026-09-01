<?php
require_once __DIR__ . '/../../controllers/customer/order_track_controller.php';

$pageTitle = 'FoodHub - Track Order #' . $order['order_id'];
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$st = $order['order_status'];

// Map status to 1-5 index for stepper
$step_index = 1;
if ($st === 'Pending') $step_index = 1;
elseif ($st === 'Preparing') $step_index = 2;
elseif ($st === 'Ready for Delivery') $step_index = 3;
elseif ($st === 'Out for Delivery') $step_index = 4;
elseif ($st === 'Delivered') $step_index = 5;
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
            <a href="order_history.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; display: inline-block; margin-bottom: 6px;">
                ← Back to Order History
            </a>
            <h1 class="page-title">Order Status: #<?php echo $order['order_id']; ?></h1>
            <p class="page-subtitle">Placed on <?php echo htmlspecialchars($order['created_at']); ?> at <strong><?php echo htmlspecialchars($order['restaurant_name']); ?></strong></p>
        </div>

        <?php if ($can_cancel): ?>
            <form action="actions/cancel_order.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <input type="hidden" name="redirect_url" value="order_track.php?order_id=<?php echo $order['order_id']; ?>">
                <button type="submit" class="btn btn-danger">Cancel Order</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Status Stepper Card -->
    <div class="card" style="margin-bottom: 28px; padding: 24px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 4px;">Fulfillment Progress</h2>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Current Status: <strong><?php echo htmlspecialchars($st); ?></strong></p>

        <?php if ($st === 'Cancelled'): ?>
            <div class="alert alert-error" style="margin-top: 16px;">
                <span>❌</span>
                <span>This order was cancelled and will not be delivered.</span>
            </div>
        <?php else: ?>
            <div class="timeline-stepper">
                <div class="timeline-step <?php echo ($step_index >= 1) ? (($step_index > 1) ? 'completed' : 'active') : ''; ?>">
                    <div class="step-icon">📋</div>
                    <div class="step-title">Order Placed</div>
                </div>

                <div class="timeline-step <?php echo ($step_index >= 2) ? (($step_index > 2) ? 'completed' : 'active') : ''; ?>">
                    <div class="step-icon">🍳</div>
                    <div class="step-title">Kitchen Preparing</div>
                </div>

                <div class="timeline-step <?php echo ($step_index >= 3) ? (($step_index > 3) ? 'completed' : 'active') : ''; ?>">
                    <div class="step-icon">📦</div>
                    <div class="step-title">Ready for Delivery</div>
                </div>

                <div class="timeline-step <?php echo ($step_index >= 4) ? (($step_index > 4) ? 'completed' : 'active') : ''; ?>">
                    <div class="step-icon">🛵</div>
                    <div class="step-title">Out for Delivery</div>
                </div>

                <div class="timeline-step <?php echo ($step_index >= 5) ? 'completed' : ''; ?>">
                    <div class="step-icon">🎉</div>
                    <div class="step-title">Delivered</div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="cart-layout">
        <!-- Left: Items Breakdown & Reviews -->
        <div>
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Ordered Items (<?php echo count($order['items']); ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Food Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <?php if ($st === 'Delivered'): ?>
                                    <th>Review</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $it): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($it['item_name']); ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($it['category']); ?></div>
                                </td>
                                <td><?php echo $it['quantity']; ?></td>
                                <td>৳<?php echo number_format((float)$it['price'], 2); ?></td>
                                <td><strong>৳<?php echo number_format((float)$it['subtotal'], 2); ?></strong></td>
                                
                                <?php if ($st === 'Delivered'): ?>
                                <td>
                                    <?php if (!empty($it['review_id'])): ?>
                                        <div style="font-size: 0.85rem; color: #10b981; font-weight: 700;">
                                            ★ <?php echo $it['user_rating']; ?>/5 (Reviewed)
                                        </div>
                                    <?php else: ?>
                                        <button 
                                            type="button" 
                                            class="btn btn-primary btn-sm" 
                                            style="padding: 4px 8px; font-size: 0.8rem; background: #f59e0b; border-color: #f59e0b;"
                                            onclick="document.getElementById('review-form-<?php echo $it['item_id']; ?>').style.display = 'block'; this.style.display = 'none';"
                                        >
                                            ⭐ Leave Review
                                        </button>

                                        <!-- Inline Review Form -->
                                        <div id="review-form-<?php echo $it['item_id']; ?>" style="display: none; margin-top: 8px; padding: 12px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                            <form action="actions/submit_review.php" method="POST">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo $it['item_id']; ?>">
                                                <input type="hidden" name="redirect_url" value="order_track.php?order_id=<?php echo $order['order_id']; ?>">

                                                <div style="margin-bottom: 8px;">
                                                    <label style="font-size: 0.8rem; font-weight: 700; display: block; margin-bottom: 4px;">Rating (1 to 5 Stars):</label>
                                                    <select name="rating" class="form-control" style="padding: 4px 8px; font-size: 0.85rem;" required>
                                                        <option value="5">⭐⭐⭐⭐⭐ (5 - Outstanding)</option>
                                                        <option value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
                                                        <option value="3">⭐⭐⭐ (3 - Good / Average)</option>
                                                        <option value="2">⭐⭐ (2 - Below Expectations)</option>
                                                        <option value="1">⭐ (1 - Terrible)</option>
                                                    </select>
                                                </div>

                                                <div style="margin-bottom: 8px;">
                                                    <label style="font-size: 0.8rem; font-weight: 700; display: block; margin-bottom: 4px;">Your Comments:</label>
                                                    <textarea name="comment" class="form-control" rows="2" placeholder="Taste, portion size, packaging..." style="font-size: 0.85rem;" required></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">Submit Review</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Delivery & Rider Info Card -->
        <div>
            <div class="card" style="margin-bottom: 24px; padding: 20px;">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 14px;">🛵 Delivery Information</h3>
                
                <div style="margin-bottom: 12px;">
                    <span style="font-size: 0.82rem; color: var(--text-muted); display: block;">Delivery Address</span>
                    <strong style="font-size: 0.95rem;"><?php echo htmlspecialchars($order['delivery_address']); ?></strong>
                </div>

                <div style="margin-bottom: 12px;">
                    <span style="font-size: 0.82rem; color: var(--text-muted); display: block;">Assigned Rider</span>
                    <?php if (!empty($order['rider_name'])): ?>
                        <div style="font-weight: 700; color: var(--text-main);">
                            🚴 <?php echo htmlspecialchars($order['rider_name']); ?>
                        </div>
                        <?php if (!empty($order['rider_phone'])): ?>
                            <div style="font-size: 0.85rem; color: var(--primary);">📞 <?php echo htmlspecialchars($order['rider_phone']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Assigning nearest rider...</span>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 12px;">
                    <span style="font-size: 0.82rem; color: var(--text-muted); display: block;">Payment Method</span>
                    <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                    <span class="badge <?php echo ($order['payment_status'] === 'Paid') ? 'badge-delivered' : 'badge-pending'; ?>" style="margin-left: 6px;">
                        <?php echo htmlspecialchars($order['payment_status']); ?>
                    </span>
                </div>

                <div style="border-top: 1px dashed var(--border-color); padding-top: 14px; margin-top: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 800;">
                        <span>Total Paid</span>
                        <span style="color: var(--primary);">৳<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
