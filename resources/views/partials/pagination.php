<?php if ($page->pages > 1): ?>
    <nav class="pagination" aria-label="Pages">
        <?php if ($page->page > 1): ?>
            <a class="btn btn-secondary btn-sm" href="<?= $this->url($path, array_filter($query ?? [], 'strlen') + ['page' => $page->page - 1]) ?>">Previous</a>
        <?php endif; ?>
        <span class="muted small">Page <?= $page->page ?> of <?= $page->pages ?> (<?= $page->total ?> total)</span>
        <?php if ($page->page < $page->pages): ?>
            <a class="btn btn-secondary btn-sm" href="<?= $this->url($path, array_filter($query ?? [], 'strlen') + ['page' => $page->page + 1]) ?>">Next</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
