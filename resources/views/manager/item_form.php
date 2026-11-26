<?php $title = 'Edit menu item'; ?>
<div class="page-head"><div><h1>Edit <?= e($item['name']) ?></h1><p><?= e($restaurant['name']) ?></p></div><a class="btn btn-secondary" href="<?= $this->url('/manager/restaurants/' . $restaurant['restaurant_id'] . '/menu') ?>">Back to menu</a></div>
<section class="card">
    <form method="POST" action="<?= $this->url('/manager/items/' . $item['item_id']) ?>" enctype="multipart/form-data">
        <?= $this->csrf() ?>
        <?php if ($item['image_path']): ?><img class="preview" src="<?= $this->asset($item['image_path']) ?>" alt="Current image"><?php endif; ?>
        <?= $this->partial('partials/food_fields', ['item' => $item]) ?>
        <button class="btn" type="submit">Save changes</button>
    </form>
</section>
