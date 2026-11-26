<?php $title = 'Admin dashboard'; ?>
<div class="page-head"><div><h1>Platform overview</h1></div></div>
<div class="grid grid-stats">
    <div class="card stat"><span>Users</span><strong><?= array_sum($userCounts) ?></strong><small class="muted"><?= $userCounts['customer'] ?> customers, <?= $userCounts['manager'] ?> managers, <?= $userCounts['rider'] ?> riders</small></div>
    <div class="card stat"><span>Restaurants to review</span><strong><?= $pendingRestaurants ?></strong><a class="small" href="<?= $this->url('/admin/restaurants', ['status' => 'pending']) ?>">Review</a></div>
    <div class="card stat"><span>Orders</span><strong><?= $orderCount ?></strong></div>
    <div class="card stat"><span>Delivered order value</span><strong><?= money($revenue) ?></strong></div>
</div>
<section class="card table-wrap">
    <h2>Accounts waiting for approval (<?= count($pendingUsers) ?>)</h2>
    <table>
        <thead><tr><th>Name</th><th>Role</th><th>Contact</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$pendingUsers): ?><tr><td colspan="4" class="empty">No accounts are waiting.</td></tr><?php endif; ?>
        <?php foreach ($pendingUsers as $account): ?>
            <tr>
                <td><?= e($account['name']) ?><div class="muted small">@<?= e($account['username']) ?></div></td>
                <td><?= e(status_label($account['role'])) ?></td>
                <td class="small"><?= e($account['email']) ?><br><?= e($account['phone']) ?></td>
                <td>
                    <div class="row">
                        <form class="inline" method="POST" action="<?= $this->url('/admin/users/' . $account['user_id'] . '/approve') ?>">
                            <?= $this->csrf() ?>
                            <button class="btn btn-sm" type="submit">Approve</button>
                        </form>
                        <form class="inline" method="POST" action="<?= $this->url('/admin/users/' . $account['user_id'] . '/delete') ?>" data-confirm="Reject and delete this registration?">
                            <?= $this->csrf() ?>
                            <button class="btn btn-danger btn-sm" type="submit">Reject</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card section table-wrap">
    <h2>Recent orders</h2>
    <table>
        <thead><tr><th>Order</th><th>Customer</th><th>Restaurant</th><th>Status</th><th class="right">Total</th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td><a href="<?= $this->url('/admin/orders/' . $order['order_id']) ?>">#<?= (int) $order['order_id'] ?></a></td>
                <td><?= e($order['customer_name']) ?></td>
                <td><?= e($order['restaurant_name']) ?></td>
                <td><span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                <td class="right"><?= money($order['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
