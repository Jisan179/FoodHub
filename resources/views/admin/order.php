<?php $title = 'Order #' . $order['order_id']; $base = '/admin/orders/' . $order['order_id']; ?>
<div class="page-head">
    <div><h1>Order #<?= (int) $order['order_id'] ?> <span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></h1><p><?= e($order['customer_name']) ?> from <?= e($order['restaurant_name']) ?></p></div>
    <a class="btn btn-secondary" href="<?= $this->url('/admin/orders') ?>">Back to orders</a>
</div>
<div class="split">
    <div class="stack"><?= $this->partial('partials/order_detail', ['order' => $order]) ?></div>
    <div class="stack">
        <section class="card">
            <h2>Order status</h2>
            <?php if (!$options): ?><p class="muted small">No manual change is possible. Pickup and delivery are confirmed by the rider.</p><?php endif; ?>
            <div class="row">
                <?php foreach ($options as $to): ?>
                    <form class="inline" method="POST" action="<?= $this->url($base . '/status') ?>"<?= $to === 'cancelled' ? ' data-confirm="Cancel this order?"' : '' ?>>
                        <?= $this->csrf() ?>
                        <input type="hidden" name="status" value="<?= e($to) ?>">
                        <button class="btn btn-sm<?= $to === 'cancelled' ? ' btn-danger' : '' ?>" type="submit"><?= $to === 'cancelled' ? 'Cancel order' : 'Mark ' . e(status_label($to)) ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <?php if (!in_array($order['status'], ['delivered', 'cancelled'], true) && in_array($order['delivery_status'], ['waiting', 'assigned'], true)): ?>
            <section class="card">
                <h2>Assign rider</h2>
                <form method="POST" action="<?= $this->url($base . '/rider') ?>">
                    <?= $this->csrf() ?>
                    <div class="field"><label>Rider<select name="rider_id"><option value="0">Not assigned</option><?php foreach ($riders as $rider): ?><option value="<?= (int) $rider['user_id'] ?>"<?= (int) $order['rider_id'] === (int) $rider['user_id'] ? ' selected' : '' ?>><?= e($rider['name']) ?></option><?php endforeach; ?></select></label></div>
                    <button class="btn btn-sm" type="submit">Save rider</button>
                </form>
            </section>
        <?php endif; ?>
    </div>
</div>
