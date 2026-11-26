<?php $title = 'Cart'; ?>
<div class="page-head"><div><h1>Your cart</h1><?php if ($cart['items']): ?><p>From <a href="<?= $this->url('/customer/restaurants/' . $cart['restaurant_id']) ?>"><?= e($cart['restaurant_name']) ?></a></p><?php endif; ?></div></div>
<?php if (!$cart['items']): ?>
    <p class="card empty">Your cart is empty. <a href="<?= $this->url('/customer/restaurants') ?>">Browse restaurants</a></p>
<?php else: ?>
<div class="split">
    <section class="card table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Price</th><th>Quantity</th><th class="right">Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($cart['items'] as $item): ?>
                <tr>
                    <td>
                        <div class="dish">
                            <?= $this->partial('partials/thumb', ['path' => $item['image_path'], 'name' => $item['name']]) ?>
                            <div><strong><?= e($item['name']) ?></strong><?php if (!$item['can_order']): ?><div><span class="badge badge-danger">Unavailable</span></div><?php endif; ?></div>
                        </div>
                    </td>
                    <td><?= money($item['price']) ?></td>
                    <td>
                        <form class="row" method="POST" action="<?= $this->url('/customer/cart/' . $item['item_id']) ?>">
                            <?= $this->csrf() ?>
                            <input class="input-qty" type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="50" aria-label="Quantity of <?= e($item['name']) ?>">
                            <button class="btn btn-secondary btn-sm" type="submit">Update</button>
                        </form>
                    </td>
                    <td class="right"><?= money($item['price'] * $item['quantity']) ?></td>
                    <td class="right">
                        <form class="inline" method="POST" action="<?= $this->url('/customer/cart/' . $item['item_id'] . '/remove') ?>">
                            <?= $this->csrf() ?>
                            <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <aside class="card">
        <h2>Summary</h2>
        <dl class="totals">
            <dt>Subtotal</dt><dd><?= money($cart['subtotal']) ?></dd>
            <dt>Delivery fee</dt><dd><?= money($cart['delivery_fee']) ?></dd>
            <dt class="grand">Total</dt><dd class="grand"><?= money($cart['total']) ?></dd>
        </dl>
        <div class="section">
            <?php if ($cart['can_checkout']): ?>
                <a class="btn btn-block" href="<?= $this->url('/customer/checkout') ?>">Go to checkout</a>
            <?php else: ?>
                <div class="alert alert-warning">Remove unavailable items, or wait until the restaurant opens.</div>
            <?php endif; ?>
        </div>
    </aside>
</div>
<?php endif; ?>
