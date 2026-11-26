<?php $filled = (int) round((float) $rating); ?>
<span class="stars" role="img" aria-label="<?= e($rating) ?> out of 5 stars"><?php for ($i = 1; $i <= 5; $i++): ?><span class="<?= $i <= $filled ? 'on' : 'off' ?>" aria-hidden="true">★</span><?php endfor; ?></span>
