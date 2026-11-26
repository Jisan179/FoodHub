<?php $title = $restaurant['name']; $path = '/customer/restaurants/' . $restaurant['restaurant_id']; ?>
<?php if ($restaurant['image_path']): ?>
    <img class="cover" src="<?= $this->asset($restaurant['image_path']) ?>" alt="<?= e($restaurant['name']) ?>">
<?php endif; ?>
<div class="page-head">
    <div>
        <h1><?= e($restaurant['name']) ?></h1>
        <p><?= e($restaurant['cuisine_type']) ?> · <?= e($restaurant['address']) ?> · <?= e($restaurant['phone']) ?></p>
        <p><?= e($restaurant['description']) ?></p>
    </div>
    <form method="POST" action="<?= $this->url($path . '/favorite') ?>">
        <?= $this->csrf() ?>
        <button class="btn btn-secondary" type="submit"><?= $restaurant['is_favorite'] ? '❤️ In favorites' : '🤍 Add to favorites' ?></button>
    </form>
</div>
<?php if (!$restaurant['is_open']): ?>
    <div class="alert alert-warning">This restaurant is closed right now. You can browse the menu, but ordering is paused.</div>
<?php endif; ?>
<?php if ($conflict): ?>
    <div class="alert alert-warning">
        <p><?= e($conflict['message']) ?></p>
        <form class="inline" method="POST" action="<?= $this->url('/customer/cart') ?>">
            <?= $this->csrf() ?>
            <input type="hidden" name="item_id" value="<?= (int) $conflict['item_id'] ?>">
            <input type="hidden" name="quantity" value="<?= (int) $conflict['quantity'] ?>">
            <input type="hidden" name="replace" value="1">
            <button class="btn btn-sm" type="submit">Empty my cart and add this item</button>
        </form>
    </div>
<?php endif; ?>
<nav class="chips" aria-label="Categories">
    <a href="<?= $this->url($path) ?>" aria-current="<?= $category === '' ? 'true' : 'false' ?>">All</a>
    <?php foreach ($categories as $option): ?>
        <a href="<?= $this->url($path, ['category' => $option]) ?>" aria-current="<?= $category === $option ? 'true' : 'false' ?>"><?= e($option) ?></a>
    <?php endforeach; ?>
</nav>
<?php if (!$items): ?><p class="card empty">No dishes are available here.</p><?php endif; ?>
<div class="grid grid-cards">
    <?php foreach ($items as $item): ?>
        <article class="card card-media">
            <?php if ($item['image_path']): ?>
                <img class="media" src="<?= $this->asset($item['image_path']) ?>" alt="<?= e($item['name']) ?>">
            <?php else: ?>
                <div class="media media-empty" aria-hidden="true"><?= food_emoji($item['name']) ?></div>
            <?php endif; ?>
            <div class="card-body">
                <div class="row-between"><h3><?= e($item['name']) ?></h3><span class="badge"><?= e($item['category']) ?></span></div>
                <p class="small muted"><?= e($item['description']) ?></p>
                <?php if ($item['review_count'] > 0): ?><div class="row small"><?= $this->partial('partials/stars', ['rating' => $item['avg_rating']]) ?><span class="muted"><?= e($item['avg_rating']) ?> (<?= (int) $item['review_count'] ?>)</span></div><?php endif; ?>
                <div class="row-between card-actions">
                    <strong><?= money($item['price']) ?></strong>
                    <form class="row" method="POST" action="<?= $this->url('/customer/cart') ?>">
                        <?= $this->csrf() ?>
                        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                        <input class="input-qty" type="number" name="quantity" value="1" min="1" max="50" aria-label="Quantity of <?= e($item['name']) ?>">
                        <button class="btn btn-sm" type="submit"<?= $restaurant['is_open'] ? '' : ' disabled' ?>>Add</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
