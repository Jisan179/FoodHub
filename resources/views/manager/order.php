<?php $title = 'Order #' . $order['order_id']; ?>
<div class="page-head">
    <div><h1>Order #<?= (int) $order['order_id'] ?> <span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></h1><p><?= e($order['restaurant_name']) ?> · <?= e($order['customer_name']) ?></p></div>
    <div class="row">
        <?php foreach ($options as $to): ?>
            <form class="inline" method="POST" action="<?= $this->url('/manager/orders/' . $order['order_id'] . '/status') ?>"<?= $to === 'cancelled' ? ' data-confirm="Reject this order?"' : '' ?>>
                <?= $this->csrf() ?>
                <input type="hidden" name="status" value="<?= e($to) ?>">
                <button class="btn<?= $to === 'cancelled' ? ' btn-danger' : '' ?>" type="submit"><?= $to === 'cancelled' ? 'Reject order' : 'Mark ' . e(status_label($to)) ?></button>
            </form>
        <?php endforeach; ?>
        <a class="btn btn-secondary" href="<?= $this->url('/manager/orders') ?>">Back</a>
    </div>
</div>
<div class="stack"><?= $this->partial('partials/order_detail', ['order' => $order]) ?></div>
