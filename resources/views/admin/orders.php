<?php $title = 'Orders'; ?>
<div class="page-head"><div><h1>Orders and deliveries</h1></div></div>
<nav class="chips" aria-label="Filter by status">
    <?php foreach (['' => 'All', 'pending' => 'Pending', 'preparing' => 'Preparing', 'ready' => 'Ready', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $value => $label): ?>
        <a href="<?= $this->url('/admin/orders', $value === '' ? [] : ['status' => $value]) ?>" aria-current="<?= $status === $value ? 'true' : 'false' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>
<section class="card table-wrap">
    <table>
        <thead><tr><th>Order</th><th>Customer</th><th>Restaurant</th><th>Order</th><th>Delivery</th><th>Rider</th><th class="right">Total</th></tr></thead>
        <tbody>
        <?php if (!$orders): ?><tr><td colspan="7" class="empty">No orders found.</td></tr><?php endif; ?>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><a href="<?= $this->url('/admin/orders/' . $order['order_id']) ?>">#<?= (int) $order['order_id'] ?></a></td>
                <td><?= e($order['customer_name']) ?></td>
                <td><?= e($order['restaurant_name']) ?></td>
                <td><span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                <td><span class="<?= status_badge($order['delivery_status']) ?>"><?= e(status_label($order['delivery_status'])) ?></span></td>
                <td><?= $order['rider_name'] ? e($order['rider_name']) : '-' ?></td>
                <td class="right"><?= money($order['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?= $this->partial('partials/pagination', ['page' => $page, 'path' => '/admin/orders', 'query' => ['status' => $status]]) ?>
