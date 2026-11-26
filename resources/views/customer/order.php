<?php
$title = 'Order #' . $order['order_id'];
$steps = ['pending' => 'Order placed', 'preparing' => 'Preparing', 'ready' => 'Ready for delivery', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered'];
$position = array_search($order['status'], array_keys($steps), true);
?>
<div class="page-head">
    <div><h1>Order #<?= (int) $order['order_id'] ?></h1><p><?= e($order['restaurant_name']) ?> · <?= e(format_datetime($order['created_at'])) ?></p></div>
    <?php if ($canCancel): ?>
        <form method="POST" action="<?= $this->url('/customer/orders/' . $order['order_id'] . '/cancel') ?>" data-confirm="Cancel this order?">
            <?= $this->csrf() ?>
            <button class="btn btn-danger" type="submit">Cancel order</button>
        </form>
    <?php endif; ?>
</div>
<?php if ($order['status'] === 'cancelled'): ?>
    <div class="alert alert-error">This order was cancelled.</div>
<?php else: ?>
    <ol class="steps card">
        <?php $index = 0; foreach ($steps as $label): ?>
            <li<?= $index <= $position ? ' class="done"' : '' ?>><?= e($label) ?></li>
        <?php $index++; endforeach; ?>
    </ol>
<?php endif; ?>
<div class="stack section">
    <?= $this->partial('partials/order_detail', ['order' => $order]) ?>
    <?php if ($order['status'] === 'delivered'): ?>
        <p><a class="btn" href="<?= $this->url('/customer/reviews') ?>">Review your food</a></p>
    <?php endif; ?>
</div>
