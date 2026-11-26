<?php $title = 'Manager dashboard'; ?>
<div class="page-head">
    <div><h1>Manager dashboard</h1><p>Your restaurants and sales.</p></div>
    <div class="row">
        <a class="btn" href="<?= $this->url('/manager/restaurants/create') ?>">Register restaurant</a>
        <a class="btn btn-secondary" href="<?= $this->url('/manager/orders') ?>">View orders</a>
    </div>
</div>
<div class="grid grid-stats">
    <div class="card stat"><span>Restaurants</span><strong><?= count($restaurants) ?></strong></div>
    <div class="card stat"><span>All orders</span><strong><?= $orderCount ?></strong></div>
    <div class="card stat"><span>Waiting on kitchen or pickup</span><strong><?= $openCount ?></strong></div>
    <div class="card stat"><span>Delivered revenue</span><strong><?= money($revenue) ?></strong></div>
</div>
<section class="card">
    <h2>My restaurants</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Restaurant</th><th>Approval</th><th>Taking orders</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$restaurants): ?><tr><td colspan="4" class="empty">You have not registered a restaurant yet.</td></tr><?php endif; ?>
        <?php foreach ($restaurants as $restaurant): $base = '/manager/restaurants/' . $restaurant['restaurant_id']; ?>
            <tr>
                <td><strong><?= e($restaurant['name']) ?></strong><div class="muted small"><?= e($restaurant['address']) ?></div></td>
                <td>
                    <span class="<?= status_badge($restaurant['status']) ?>"><?= e(status_label($restaurant['status'])) ?></span>
                    <?php if ($restaurant['status'] === 'rejected'): ?><div class="small">Reason: <?= e($restaurant['rejection_reason']) ?></div><?php endif; ?>
                </td>
                <td><span class="badge <?= $restaurant['is_open'] ? 'badge-success' : 'badge-danger' ?>"><?= $restaurant['is_open'] ? 'Open' : 'Closed' ?></span></td>
                <td>
                    <div class="row">
                        <form class="inline" method="POST" action="<?= $this->url($base . '/toggle') ?>">
                            <?= $this->csrf() ?>
                            <button class="btn btn-sm" type="submit"><?= $restaurant['is_open'] ? 'Close for orders' : 'Open for orders' ?></button>
                        </form>
                        <a class="btn btn-secondary btn-sm" href="<?= $this->url($base . '/menu') ?>">Menu</a>
                        <a class="btn btn-secondary btn-sm" href="<?= $this->url($base . '/edit') ?>">Edit</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php $weekOrders = array_sum(array_column($trend, 'orders')); ?>
<div class="split section" data-auto-refresh="30">
    <section class="card">
        <h2>Order trend, last 7 days</h2>
        <p class="muted small">New orders appear as soon as they are placed. Cancelled orders are not counted. This section updates every 30 seconds.</p>
        <?php if ($weekOrders === 0): ?>
            <div class="empty">
                <div class="empty-art" aria-hidden="true">📊</div>
                <p>No orders in the last 7 days. The trend appears with your first order.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap"><table class="sales-table">
                <thead><tr><th>Day</th><th>Orders</th><th>Dishes</th><th class="trend">Trend</th><th class="right">Food value</th></tr></thead>
                <tbody>
                <?php foreach ($trend as $day => $row): $isToday = $day === date('Y-m-d'); ?>
                    <tr<?= $isToday ? ' class="today"' : '' ?>>
                        <td><?= $isToday ? '<strong>Today</strong>' : e(date('D, d M', strtotime($day))) ?></td>
                        <td><?= (int) $row['orders'] ?></td>
                        <td><?= (int) $row['items'] ?></td>
                        <td class="trend">
                            <?php if ($row['value'] > 0): ?>
                                <progress max="<?= e($maxValue) ?>" value="<?= e($row['value']) ?>" aria-label="Food value on <?= e($day) ?>"></progress>
                            <?php else: ?>
                                <span class="muted small">No orders</span>
                            <?php endif; ?>
                        </td>
                        <td class="right"><?= money($row['value']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th>Total</th><th><?= $weekOrders ?></th><th><?= array_sum(array_column($trend, 'items')) ?></th><th class="trend"></th><th class="right"><?= money(array_sum(array_column($trend, 'value'))) ?></th></tr></tfoot>
            </table></div>
        <?php endif; ?>
    </section>
    <div class="stack">
        <section class="card" id="trending-today">
            <h2>🔥 Trending today</h2>
            <?= $this->partial('partials/ranking', ['items' => $trendingToday, 'empty' => 'No orders yet today.']) ?>
        </section>
        <section class="card">
            <h2>Top dishes, last 7 days</h2>
            <?= $this->partial('partials/ranking', ['items' => $topWeek, 'empty' => 'No orders this week.']) ?>
        </section>
    </div>
</div>
