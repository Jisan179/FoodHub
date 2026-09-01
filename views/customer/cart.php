<?php
require_once __DIR__ . '/../../controllers/customer/cart_controller.php';

$pageTitle = 'FoodHub - My Shopping Cart';
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
            <h1 class="page-title">🛒 Shopping Cart</h1>
            <p class="page-subtitle">Review selected items before confirming your order.</p>
        </div>
        <?php if (!empty($cart_summary['restaurant_id'])): ?>
            <a href="view_menu.php?restaurant_id=<?php echo $cart_summary['restaurant_id']; ?>" class="btn btn-secondary btn-sm">
                + Add More Items
            </a>
        <?php endif; ?>
    </div>

    <?php if ($cart_summary['total_items'] > 0): ?>
    <!-- Restaurant Header Notice -->
    <div class="card" style="border-left: 4px solid var(--primary); margin-bottom: 24px; padding: 14px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <strong>Ordering from:</strong> 
                <span style="color: var(--primary); font-weight: 700; font-size: 1.05rem;">
                    <?php echo htmlspecialchars($cart_summary['restaurant_name']); ?>
                </span>
            </div>
            <a href="view_menu.php?restaurant_id=<?php echo $cart_summary['restaurant_id']; ?>" style="font-size: 0.88rem; color: var(--primary); text-decoration: none;">
                View Restaurant Menu →
            </a>
        </div>
    </div>

    <div class="cart-layout">
        <!-- Cart Items List -->
        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_summary['items'] as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </div>
                            </td>
                            <td>৳<?php echo number_format((float)$item['price'], 2); ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <!-- Decrement Form -->
                                    <form action="actions/update_cart_item.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo intval($item['quantity']) - 1; ?>">
                                        <button type="submit" class="qty-btn" title="Decrease quantity">−</button>
                                    </form>

                                    <span class="qty-val"><?php echo $item['quantity']; ?></span>

                                    <!-- Increment Form -->
                                    <form action="actions/update_cart_item.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo intval($item['quantity']) + 1; ?>">
                                        <button type="submit" class="qty-btn" title="Increase quantity">+</button>
                                    </form>
                                </div>
                            </td>
                            <td><strong>৳<?php echo number_format((float)$item['subtotal'], 2); ?></strong></td>
                            <td>
                                <form action="actions/remove_from_cart.php" method="POST" onsubmit="return confirm('Remove this item from your cart?');">
                                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 10px;">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="summary-card">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: var(--text-main);">
                Order Summary
            </h2>
            
            <div class="summary-row">
                <span>Total Items</span>
                <span><?php echo $cart_summary['total_items']; ?></span>
            </div>

            <div class="summary-row">
                <span>Items Subtotal</span>
                <span>৳<?php echo number_format((float)$cart_summary['subtotal'], 2); ?></span>
            </div>

            <div class="summary-row">
                <span>Standard Delivery Fee</span>
                <span>৳<?php echo number_format((float)$cart_summary['delivery_fee'], 2); ?></span>
            </div>

            <div class="summary-row total">
                <span>Estimated Total</span>
                <span style="color: var(--primary);">৳<?php echo number_format((float)$cart_summary['grand_total'], 2); ?></span>
            </div>

            <div style="margin-top: 24px;">
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; text-align: center;">
                    Proceed to Checkout →
                </a>
            </div>

            <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 14px;">
                🔒 Prices are verified & calculated securely by the server during checkout.
            </p>
        </div>
    </div>

    <?php else: ?>
    <!-- Empty Cart State -->
    <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <div class="empty-title">Your Cart is Currently Empty</div>
        <div class="empty-desc">Looks like you haven't added any appetizing meals yet. Explore our restaurant partners and satisfy your hunger!</div>
        <a href="browse_restaurants.php" class="btn btn-primary">Browse Restaurants</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
