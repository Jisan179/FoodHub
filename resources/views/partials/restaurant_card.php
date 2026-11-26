<article class="card card-media">
    <?php if ($restaurant['image_path']): ?>
        <img class="media" src="<?= $this->asset($restaurant['image_path']) ?>" alt="">
    <?php else: ?>
        <div class="media media-empty" aria-hidden="true"><?= food_emoji($restaurant['name'] . ' ' . $restaurant['cuisine_type']) ?></div>
    <?php endif; ?>
    <div class="card-body">
        <div class="row-between">
            <h3><?= e($restaurant['name']) ?></h3>
            <form class="inline" method="POST" action="<?= $this->url('/customer/restaurants/' . $restaurant['restaurant_id'] . '/favorite') ?>">
                <?= $this->csrf() ?>
                <button class="btn btn-secondary btn-sm" type="submit" aria-label="<?= $restaurant['is_favorite'] ? 'Remove from favorites' : 'Add to favorites' ?>"><?= $restaurant['is_favorite'] ? '❤️' : '🤍' ?></button>
            </form>
        </div>
        <p class="muted small"><?= e($restaurant['cuisine_type']) ?> · <?= e($restaurant['address']) ?></p>
        <p class="small"><?= e($restaurant['description']) ?></p>
        <div class="row small">
            <?php if ($restaurant['review_count'] > 0): ?>
                <?= $this->partial('partials/stars', ['rating' => $restaurant['avg_rating']]) ?><strong><?= e($restaurant['avg_rating']) ?></strong><span class="muted">(<?= (int) $restaurant['review_count'] ?>)</span>
            <?php else: ?>
                <span class="badge">No reviews yet</span>
            <?php endif; ?>
            <?php if (!$restaurant['is_open']): ?><span class="badge badge-danger">Closed</span><?php endif; ?>
            <span class="muted"><?= (int) $restaurant['item_count'] ?> items</span>
        </div>
        <div class="card-actions">
            <a class="btn btn-block" href="<?= $this->url('/customer/restaurants/' . $restaurant['restaurant_id']) ?>">View menu</a>
        </div>
    </div>
</article>
