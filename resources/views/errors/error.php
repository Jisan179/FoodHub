<section class="card auth">
    <p class="muted">Error <?= (int) $status ?></p>
    <h1><?= e($title) ?></h1>
    <?php if ($message !== ''): ?><p><?= e($message) ?></p><?php endif; ?>
    <a class="btn" href="<?= $this->url('/') ?>">Go to the home page</a>
</section>
