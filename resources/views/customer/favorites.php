<?php $title = 'Favorites'; ?>
<div class="page-head"><div><h1>Favorite restaurants</h1></div></div>
<?php if (!$restaurants): ?>
    <p class="card empty">You have no favorites yet. <a href="<?= $this->url('/customer/restaurants') ?>">Find a restaurant</a></p>
<?php endif; ?>
<div class="grid grid-cards">
    <?php foreach ($restaurants as $restaurant): ?>
        <?= $this->partial('partials/restaurant_card', ['restaurant' => $restaurant]) ?>
    <?php endforeach; ?>
</div>
