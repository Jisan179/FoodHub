<?php $title = 'Checkout'; $method = $this->rawOld('payment_method', 'cod'); ?>
<div class="page-head"><div><h1>Checkout</h1><p>Ordering from <?= e($cart['restaurant_name']) ?></p></div></div>
<div class="split">
    <section class="card">
        <form method="POST" action="<?= $this->url('/customer/checkout') ?>">
            <?= $this->csrf() ?>
            <div class="field"><label for="delivery_address">Delivery address</label><input id="delivery_address" name="delivery_address" value="<?= $this->old('delivery_address', $currentUser['address']) ?>" maxlength="255" required></div>
            <div class="field"><label for="contact_phone">Contact phone</label><input id="contact_phone" name="contact_phone" type="tel" value="<?= $this->old('contact_phone', $currentUser['phone']) ?>" required></div>
            <fieldset class="field">
                <legend class="label">Payment method</legend>
                <label class="check"><input type="radio" name="payment_method" value="cod"<?= $method !== 'wallet' ? ' checked' : '' ?>> Cash on Delivery</label>
                <label class="check"><input type="radio" name="payment_method" value="wallet"<?= $method === 'wallet' ? ' checked' : '' ?>> Mobile Wallet (simulated, no real payment is taken)</label>
            </fieldset>
            <button class="btn btn-block" type="submit">Place order</button>
        </form>
    </section>
    <aside class="card">
        <h2>Your order</h2>
        <ul>
            <?php foreach ($cart['items'] as $item): ?>
                <li><?= (int) $item['quantity'] ?> × <?= e($item['name']) ?></li>
            <?php endforeach; ?>
        </ul>
        <dl class="totals">
            <dt>Subtotal</dt><dd><?= money($cart['subtotal']) ?></dd>
            <dt>Delivery fee</dt><dd><?= money($cart['delivery_fee']) ?></dd>
            <dt class="grand">Total</dt><dd class="grand"><?= money($cart['total']) ?></dd>
        </dl>
    </aside>
</div>
