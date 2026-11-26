<?php if (!$items): ?>
    <p class="muted"><?= e($empty) ?></p>
<?php else: ?>
    <ol class="ranking">
        <?php foreach ($items as $position => $item): ?>
            <li>
                <span class="rank rank-<?= min($position + 1, 4) ?>"><?= $position + 1 ?></span>
                <?= $this->partial('partials/thumb', ['path' => $item['image_path'], 'name' => $item['item_name']]) ?>
                <div class="rank-name"><strong><?= e($item['item_name']) ?></strong><small><?= (int) $item['sold'] ?> ordered</small></div>
                <span class="rank-value"><?= money($item['value']) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
