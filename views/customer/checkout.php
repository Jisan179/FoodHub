<?php
require_once __DIR__ . '/../../controllers/customer/checkout_controller.php';

$pageTitle = 'FoodHub - Checkout';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h1 class="page-title">🚀 Complete Your Order</h1>
            <p class="page-subtitle">Provide your delivery location and confirm payment details.</p>
        </div>
        <a href="cart.php" class="btn btn-secondary btn-sm">← Back to Cart</a>
    </div>

    <form action="actions/place_order.php" method="POST">
        <div class="cart-layout">
            <!-- Left: Delivery Details Form -->
            <div>
                <div class="card" style="margin-bottom: 24px; padding: 24px;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">
                        📍 Delivery Information
                    </h2>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" for="delivery_address">Delivery Address *</label>
                        <textarea 
                            id="delivery_address" 
                            name="delivery_address" 
                            class="form-control" 
                            rows="3" 
                            placeholder="House / Apartment no., Road, Block, Area, City..." 
                            required
                        ><?php echo htmlspecialchars($default_address); ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="form-label" for="contact_phone">Contact Phone</label>
                        <input 
                            type="text" 
                            id="contact_phone" 
                            name="contact_phone" 
                            class="form-control" 
                            placeholder="+8801..." 
                            value="<?php echo htmlspecialchars($default_phone); ?>"
                        >
                    </div>
                </div>

                <div class="card" style="padding: 24px;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">
                        💳 Payment Method
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; background: #f8fafc;">
                            <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                            <div>
                                <strong>💵 Cash on Delivery (COD)</strong>
                                <div style="font-size: 0.82rem; color: var(--text-muted);">Pay in cash when your food arrives at your door.</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                            <input type="radio" name="payment_method" value="Online Payment / Mobile Wallet">
                            <div>
                                <strong>📱 Mobile Banking / Digital Wallet (Simulated)</strong>
                                <div style="font-size: 0.82rem; color: var(--text-muted);">Instant verification via bKash / Nagad / Card.</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right: Order Review Summary -->
            <div class="summary-card">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px; color: var(--text-main);">
                    Order Details
                </h2>

                <div style="font-size: 0.9rem; color: var(--primary); font-weight: 700; margin-bottom: 16px;">
                    🏪 <?php echo htmlspecialchars($cart_summary['restaurant_name']); ?>
                </div>

                <div style="margin-bottom: 16px; max-height: 220px; overflow-y: auto;">
                    <?php foreach ($cart_summary['items'] as $item): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 8px; border-bottom: 1px dotted var(--border-color); padding-bottom: 6px;">
                        <div>
                            <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                            <span style="color: var(--text-muted); font-size: 0.8rem;">x <?php echo $item['quantity']; ?></span>
                        </div>
                        <strong>৳<?php echo number_format((float)$item['subtotal'], 2); ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Items Subtotal</span>
                    <span>৳<?php echo number_format((float)$cart_summary['subtotal'], 2); ?></span>
                </div>

                <div class="summary-row">
                    <span>Delivery Charge</span>
                    <span>৳<?php echo number_format((float)$cart_summary['delivery_fee'], 2); ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total Pay</span>
                    <span style="color: var(--primary);">৳<?php echo number_format((float)$cart_summary['grand_total'], 2); ?></span>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.05rem; font-weight: 700; margin-top: 20px;">
                    🎉 Confirm & Place Order
                </button>

                <p style="font-size: 0.78rem; color: var(--text-muted); text-align: center; margin-top: 14px;">
                    By clicking confirm, your order is submitted directly to the restaurant kitchen.
                </p>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
