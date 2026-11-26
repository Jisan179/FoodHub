<?php $title = 'Deliveries'; ?>
<div class="page-head"><div><h1>Deliveries</h1><p>Accept a trip, collect the food and deliver it.</p></div></div>
<div class="grid grid-stats">
    <div class="card stat"><span>Available</span><strong><?= count($available) ?></strong></div>
    <div class="card stat"><span>My active trips</span><strong><?= count($active) ?></strong></div>
    <div class="card stat"><span>Delivered</span><strong><?= $delivered ?></strong></div>
    <div class="card stat"><span>Earnings</span><strong><?= money($earnings) ?></strong></div>
</div>
<h2>My active trips</h2>
<?php if (!$active): ?><p class="card empty">You have no active trips.</p><?php endif; ?>
<div class="grid grid-trips">
    <?php foreach ($active as $trip): $base = '/rider/deliveries/' . $trip['delivery_id']; ?>
        <article class="card trip">
            <div class="row-between trip-head"><h3>Order #<?= (int) $trip['order_id'] ?></h3><span class="<?= status_badge($trip['status']) ?>"><?= e(status_label($trip['status'])) ?></span></div>
            <?= $this->partial('partials/route_map', ['trip' => $trip]) ?>
            <ol class="route">
                <li>
                    <span class="route-label">Pick up from</span>
                    <strong><?= e($trip['restaurant_name']) ?></strong>
                    <small><?= e($trip['restaurant_address']) ?> · <a href="tel:<?= e($trip['restaurant_phone']) ?>"><?= e($trip['restaurant_phone']) ?></a></small>
                </li>
                <li>
                    <span class="route-label">Deliver to</span>
                    <strong><?= e($trip['customer_name']) ?></strong>
                    <small><?= e($trip['delivery_address']) ?> · <a href="tel:<?= e($trip['contact_phone']) ?>"><?= e($trip['contact_phone']) ?></a></small>
                </li>
            </ol>
            <p><?= money($trip['total_amount']) ?> · <?= e(status_label($trip['payment_method'])) ?></p>
            <p class="small">Kitchen status: <?= e(status_label($trip['order_status'])) ?></p>
            <?php if ($trip['status'] === 'assigned'): ?>
                <form method="POST" action="<?= $this->url($base . '/pickup') ?>">
                    <?= $this->csrf() ?>
                    <button class="btn btn-block" type="submit"<?= $trip['order_status'] === 'ready' ? '' : ' disabled' ?>><?= $trip['order_status'] === 'ready' ? 'Confirm pickup' : 'Waiting for the kitchen' ?></button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?= $this->url($base . '/complete') ?>" data-confirm="Confirm that the food was delivered and paid for?">
                    <?= $this->csrf() ?>
                    <button class="btn btn-block" type="submit">Mark delivered</button>
                </form>
            <?php endif; ?>
            <details class="section">
                <summary>Can't complete this trip?</summary>
                <form method="POST" action="<?= $this->url($base . '/release') ?>" data-confirm="Release this delivery?">
                    <?= $this->csrf() ?>
                    <div class="field"><label>Reason<input name="reason" maxlength="200" required></label></div>
                    <button class="btn btn-danger btn-sm" type="submit">Release delivery</button>
                </form>
            </details>
        </article>
    <?php endforeach; ?>
</div>
<section class="card section table-wrap">
    <h2>Available requests</h2>
    <table>
        <thead><tr><th>Order</th><th>Pick up from</th><th>Kitchen</th><th class="right">Order value</th><th></th></tr></thead>
        <tbody>
        <?php if (!$available): ?><tr><td colspan="5" class="empty">No requests are waiting. Refresh in a few minutes.</td></tr><?php endif; ?>
        <?php foreach ($available as $request): ?>
            <tr>
                <td>#<?= (int) $request['order_id'] ?></td>
                <td><?= e($request['restaurant_name']) ?><div class="muted small"><?= e($request['restaurant_address']) ?></div></td>
                <td><span class="<?= status_badge($request['order_status']) ?>"><?= e(status_label($request['order_status'])) ?></span></td>
                <td class="right"><?= money($request['total_amount']) ?></td>
                <td class="right">
                    <form class="inline" method="POST" action="<?= $this->url('/rider/deliveries/' . $request['delivery_id'] . '/accept') ?>">
                        <?= $this->csrf() ?>
                        <button class="btn btn-sm" type="submit">Accept</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="muted small">The customer's address and phone number appear after you accept.</p>
</section>
