<?php
$title = 'Reviews';
$distribution = array_fill_keys([5, 4, 3, 2, 1], 0);
foreach ($reviews as $review) {
    $distribution[(int) $review['rating']]++;
}
$average = $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;
?>
<div class="page-head"><div><h1>Ratings and reviews</h1><p>Tap the stars to rate dishes from your delivered orders.</p></div></div>
<div class="review-layout">
    <div class="stack">
        <section>
            <h2>Waiting for your review (<?= count($pending) ?>)</h2>
            <?php if (!$pending): ?>
                <div class="card empty">
                    <div class="empty-art" aria-hidden="true">🎉</div>
                    <p>You are all caught up. New dishes appear here after an order is delivered.</p>
                    <a class="btn" href="<?= $this->url('/customer/restaurants') ?>">Order something tasty</a>
                </div>
            <?php endif; ?>
            <div class="stack">
                <?php foreach ($pending as $item): ?>
                    <form class="card review-card" method="POST" action="<?= $this->url('/customer/reviews') ?>">
                        <?= $this->csrf() ?>
                        <input type="hidden" name="order_id" value="<?= (int) $item['order_id'] ?>">
                        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                        <?= $this->partial('partials/thumb', ['path' => $item['image_path'], 'name' => $item['item_name']]) ?>
                        <div class="review-body">
                            <h3><?= e($item['item_name']) ?></h3>
                            <p class="muted small"><?= e($item['restaurant_name']) ?> · Order #<?= (int) $item['order_id'] ?></p>
                            <?= $this->partial('partials/star_input', ['id' => 'new-' . $item['order_id'] . '-' . $item['item_id'], 'selected' => 0]) ?>
                            <div class="field"><label>Comment (optional)<textarea name="comment" maxlength="1000" placeholder="What did you like?"></textarea></label></div>
                            <button class="btn" type="submit">Submit review</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="section">
            <h2>Your reviews (<?= count($reviews) ?>)</h2>
            <?php if (!$reviews): ?><p class="card empty">You have not written any reviews yet.</p><?php endif; ?>
            <div class="stack">
                <?php foreach ($reviews as $review): ?>
                    <article class="card review-card">
                        <?= $this->partial('partials/thumb', ['path' => $review['image_path'], 'name' => $review['item_name']]) ?>
                        <div class="review-body">
                            <div class="row-between">
                                <div><h3><?= e($review['item_name']) ?></h3><p class="muted small"><?= e($review['restaurant_name']) ?> · <?= e(date('d M Y', strtotime($review['created_at']))) ?></p></div>
                                <?= $this->partial('partials/stars', ['rating' => $review['rating']]) ?>
                            </div>
                            <?php if ($review['comment'] !== ''): ?><p><?= e($review['comment']) ?></p><?php endif; ?>
                            <div class="row">
                                <details class="review-edit">
                                    <summary>Edit review</summary>
                                    <form method="POST" action="<?= $this->url('/customer/reviews/' . $review['review_id']) ?>">
                                        <?= $this->csrf() ?>
                                        <?= $this->partial('partials/star_input', ['id' => 'edit-' . $review['review_id'], 'selected' => $review['rating']]) ?>
                                        <div class="field"><label>Comment<textarea name="comment" maxlength="1000"><?= e($review['comment']) ?></textarea></label></div>
                                        <button class="btn btn-sm" type="submit">Save changes</button>
                                    </form>
                                </details>
                                <form class="inline" method="POST" action="<?= $this->url('/customer/reviews/' . $review['review_id'] . '/delete') ?>" data-confirm="Delete this review?">
                                    <?= $this->csrf() ?>
                                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <aside class="card review-summary">
        <h2>Your rating summary</h2>
        <p class="score"><?= $reviews ? e(number_format($average, 1)) : '–' ?></p>
        <?= $this->partial('partials/stars', ['rating' => $average]) ?>
        <p class="muted small"><?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?> written · <?= count($pending) ?> waiting</p>
        <?php foreach ($distribution as $stars => $total): ?>
            <div class="bar-row">
                <span><?= $stars ?> ★</span>
                <progress max="<?= max(1, count($reviews)) ?>" value="<?= $total ?>" aria-label="<?= $stars ?> star reviews"></progress>
                <span class="muted"><?= $total ?></span>
            </div>
        <?php endforeach; ?>
    </aside>
</div>
