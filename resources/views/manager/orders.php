<?php $title = 'Orders'; $actions = ['pending' => ['preparing' => 'Accept order', 'cancelled' => 'Reject'], 'preparing' => ['ready' => 'Mark ready']]; ?>
<div class="page-head"><div><h1>Orders</h1><p>Accept new orders and keep the kitchen up to date.</p></div></div>
<h2>Open orders (<?= count($open) ?>)</h2>
<?php if (!$open): ?><p class="card empty">No open orders right now.</p><?php endif; ?>
<div class="grid grid-cards">
    <?php foreach ($open as $order): ?>
        <article class="card">
            <div class="row-between">
                <h3><a href="<?= $this->url('/manager/orders/' . $order['order_id']) ?>">Order #<?= (int) $order['order_id'] ?></a></h3>
                <span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
            </div>
            <p class="muted small"><?= e($order['restaurant_name']) ?> · <?= e($order['customer_name']) ?> · <?= e(format_datetime($order['created_at'])) ?></p>
            <ul>
                <?php foreach ($order['items'] as $item): ?><li><?= (int) $item['quantity'] ?> × <?= e($item['item_name']) ?></li><?php endforeach; ?>
            </ul>
            <p><strong><?= money($order['total_amount']) ?></strong><span class="muted small"> · Rider: <?= $order['rider_name'] ? e($order['rider_name']) : 'not assigned' ?></span></p>
            <div class="row">
                <?php foreach ($actions[$order['status']] ?? [] as $to => $label): ?>
                    <form class="inline" method="POST" action="<?= $this->url('/manager/orders/' . $order['order_id'] . '/status') ?>"<?= $to === 'cancelled' ? ' data-confirm="Reject this order?"' : '' ?>>
                        <?= $this->csrf() ?>
                        <input type="hidden" name="status" value="<?= $to ?>">
                        <button class="btn btn-sm<?= $to === 'cancelled' ? ' btn-danger' : '' ?>" type="submit"><?= e($label) ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<section class="card section table-wrap">
    <h2>Completed and cancelled</h2>
    <table>
        <thead><tr><th>Order</th><th>Restaurant</th><th>Customer</th><th>Status</th><th class="right">Total</th></tr></thead>
        <tbody>
        <?php if (!$history): ?><tr><td colspan="5" class="empty">Nothing here yet.</td></tr><?php endif; ?>
        <?php foreach ($history as $order): ?>
            <tr>
                <td><a href="<?= $this->url('/manager/orders/' . $order['order_id']) ?>">#<?= (int) $order['order_id'] ?></a></td>
                <td><?= e($order['restaurant_name']) ?></td>
                <td><?= e($order['customer_name']) ?></td>
                <td><span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                <td class="right"><?= money($order['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?= $this->partial('partials/pagination', ['page' => $page, 'path' => '/manager/orders', 'query' => []]) ?>
