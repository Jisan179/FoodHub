<?php $title = 'Delivery history'; ?>
<div class="page-head"><div><h1>Delivery history</h1><p>Total earnings: <?= money($earnings) ?></p></div></div>
<section class="card table-wrap">
    <table>
        <thead><tr><th>Order</th><th>Restaurant</th><th>Delivered to</th><th>Delivered at</th><th class="right">Earning</th></tr></thead>
        <tbody>
        <?php if (!$deliveries): ?><tr><td colspan="5" class="empty">No completed deliveries yet.</td></tr><?php endif; ?>
        <?php foreach ($deliveries as $trip): ?>
            <tr>
                <td>#<?= (int) $trip['order_id'] ?></td>
                <td><?= e($trip['restaurant_name']) ?></td>
                <td><?= e($trip['customer_name']) ?></td>
                <td><?= e(format_datetime($trip['delivered_at'])) ?></td>
                <td class="right"><?= money($trip['rider_earning']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?= $this->partial('partials/pagination', ['page' => $page, 'path' => '/rider/history', 'query' => []]) ?>
