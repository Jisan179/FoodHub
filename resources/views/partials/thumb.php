<?php if ($path): ?>
    <img class="thumb" src="<?= $this->asset($path) ?>" alt="">
<?php else: ?>
    <span class="thumb thumb-art" aria-hidden="true"><?= food_emoji($name) ?></span>
<?php endif; ?>
