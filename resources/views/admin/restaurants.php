<?php $title = 'Restaurants'; ?>
<div class="page-head"><div><h1>Restaurants</h1><p>Approve, reject or suspend restaurants.</p></div></div>
<nav class="chips" aria-label="Filter by status">
    <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $value => $label): ?>
        <a href="<?= $this->url('/admin/restaurants', $value === '' ? [] : ['status' => $value]) ?>" aria-current="<?= $status === $value ? 'true' : 'false' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>
<div class="grid grid-cards">
    <?php if (!$restaurants): ?><p class="card empty">No restaurants found.</p><?php endif; ?>
    <?php foreach ($restaurants as $restaurant): ?>
        <article class="card">
            <div class="row-between"><h3><?= e($restaurant['name']) ?></h3><span class="<?= status_badge($restaurant['status']) ?>"><?= e(status_label($restaurant['status'])) ?></span></div>
            <p class="small muted"><?= e($restaurant['cuisine_type']) ?> · <?= (int) $restaurant['item_count'] ?> menu items</p>
            <p class="small"><?= e($restaurant['address']) ?><br><?= e($restaurant['phone']) ?><br>Owner: <?= e($restaurant['owner_name']) ?> (<?= e($restaurant['owner_email']) ?>)</p>
            <p class="small"><?= e($restaurant['description']) ?></p>
            <?php if ($restaurant['status'] === 'rejected'): ?><p class="small">Reason: <?= e($restaurant['rejection_reason']) ?></p><?php endif; ?>
            <form method="POST" action="<?= $this->url('/admin/restaurants/' . $restaurant['restaurant_id'] . '/status') ?>">
                <?= $this->csrf() ?>
                <div class="field"><label>Status<select name="status"><?php foreach (['pending', 'approved', 'rejected', 'suspended'] as $option): ?><option value="<?= $option ?>"<?= $restaurant['status'] === $option ? ' selected' : '' ?>><?= e(status_label($option)) ?></option><?php endforeach; ?></select></label></div>
                <div class="field"><label>Reason (required when rejecting)<input name="reason" maxlength="255"></label></div>
                <button class="btn btn-sm" type="submit">Save status</button>
            </form>
        </article>
    <?php endforeach; ?>
</div>
<?= $this->partial('partials/pagination', ['page' => $page, 'path' => '/admin/restaurants', 'query' => ['status' => $status]]) ?>
