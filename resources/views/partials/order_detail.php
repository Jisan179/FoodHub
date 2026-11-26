<section class="card">
    <h2>Items</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Quantity</th><th class="right">Unit price</th><th class="right">Total</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= e($item['item_name']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td class="right"><?= money($item['unit_price']) ?></td>
                    <td class="right"><?= money($item['unit_price'] * $item['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <dl class="totals section">
        <dt>Subtotal</dt><dd><?= money($order['subtotal']) ?></dd>
        <dt>Delivery fee</dt><dd><?= money($order['delivery_fee']) ?></dd>
        <dt class="grand"><?= $order['payment_status'] === 'paid' ? 'Total paid' : 'Total payable' ?></dt><dd class="grand"><?= money($order['total_amount']) ?></dd>
    </dl>
</section>
<section class="card">
    <h2>Delivery</h2>
    <p><strong>Address:</strong> <?= e($order['delivery_address']) ?><br>
       <strong>Contact:</strong> <?= e($order['contact_phone']) ?><br>
       <strong>Payment:</strong> <?= e(status_label($order['payment_method'])) ?> <span class="<?= status_badge($order['payment_status']) ?>"><?= e(status_label($order['payment_status'])) ?></span><br>
       <strong>Rider:</strong> <?= $order['rider_name'] ? e($order['rider_name'] . ' (' . $order['rider_phone'] . ')') : 'Not assigned yet' ?></p>
</section>
<section class="card">
    <h2>History</h2>
    <ul class="history">
        <?php foreach ($order['history'] as $event): ?>
            <li>
                <strong><?= e(status_label($event['event'])) ?></strong>
                <div class="muted small"><?= e(format_datetime($event['created_at'])) ?><?= $event['actor_name'] ? ' by ' . e($event['actor_name']) : '' ?></div>
                <?php if ($event['note'] !== ''): ?><div class="small"><?= e($event['note']) ?></div><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
