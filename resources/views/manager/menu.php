<?php $title = 'Menu'; $base = '/manager/restaurants/' . $restaurant['restaurant_id']; ?>
<div class="page-head"><div><h1>Menu: <?= e($restaurant['name']) ?></h1></div><a class="btn btn-secondary" href="<?= $this->url('/manager') ?>">Back to dashboard</a></div>
<section class="card">
    <details>
        <summary>Add a menu item</summary>
        <form method="POST" action="<?= $this->url($base . '/menu') ?>" enctype="multipart/form-data">
            <?= $this->csrf() ?>
            <?= $this->partial('partials/food_fields', ['item' => null]) ?>
            <button class="btn" type="submit">Add item</button>
        </form>
    </details>
</section>
<section class="card section table-wrap">
    <table>
        <thead><tr><th></th><th>Item</th><th>Category</th><th class="right">Price</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$items): ?><tr><td colspan="6" class="empty">No menu items yet.</td></tr><?php endif; ?>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php if ($item['image_path']): ?><img class="thumb" src="<?= $this->asset($item['image_path']) ?>" alt=""><?php endif; ?></td>
                <td><strong><?= e($item['name']) ?></strong><div class="muted small"><?= e($item['description']) ?></div></td>
                <td><?= e($item['category']) ?></td>
                <td class="right"><?= money($item['price']) ?></td>
                <td><span class="badge <?= $item['is_available'] ? 'badge-success' : 'badge-danger' ?>"><?= $item['is_available'] ? 'Available' : 'Unavailable' ?></span></td>
                <td>
                    <div class="row">
                        <a class="btn btn-secondary btn-sm" href="<?= $this->url('/manager/items/' . $item['item_id'] . '/edit') ?>">Edit</a>
                        <form class="inline" method="POST" action="<?= $this->url('/manager/items/' . $item['item_id'] . '/delete') ?>" data-confirm="Delete this menu item?">
                            <?= $this->csrf() ?>
                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
