<?php
$links = [
    'customer' => ['/customer' => 'Home', '/customer/restaurants' => 'Restaurants', '/customer/favorites' => 'Favorites', '/customer/cart' => 'Cart', '/customer/orders' => 'My Orders', '/customer/reviews' => 'Reviews'],
    'manager' => ['/manager' => 'Dashboard', '/manager/orders' => 'Orders', '/manager/restaurants/create' => 'New Restaurant'],
    'rider' => ['/rider' => 'Deliveries', '/rider/history' => 'History'],
    'admin' => ['/admin' => 'Dashboard', '/admin/users' => 'Users', '/admin/restaurants' => 'Restaurants', '/admin/orders' => 'Orders'],
];
$role = $currentUser['role'] ?? null;
$isCurrent = static function (string $path) use ($currentPath): bool {
    if (in_array($path, ['/customer', '/manager', '/rider', '/admin'], true)) {
        return $currentPath === $path;
    }
    return $currentPath === $path || strpos($currentPath, $path . '/') === 0;
};
$isAuthPage = !$role && (in_array($currentPath, ['/login', '/register', '/forgot-password'], true) || strpos($currentPath, '/reset-password/') === 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(isset($title) ? $title . ' · FoodHub' : 'FoodHub') ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap">
    <link rel="stylesheet" href="<?= $this->asset('/assets/css/app.css') ?>">
    <script src="<?= $this->asset('/assets/js/app.js') ?>" defer></script>
</head>
<body<?= $isAuthPage ? ' class="auth-page"' : '' ?>>
<a class="skip-link" href="#content">Skip to content</a>
<header class="topbar">
    <div class="container">
        <a class="brand" href="<?= $this->url('/') ?>">🍔 FoodHub</a>
        <?php if ($role): ?>
            <span class="role-tag"><?= e(status_label($role)) ?></span>
        <?php endif; ?>
        <?php if (!$isAuthPage): ?>
        <button class="btn btn-secondary btn-sm nav-toggle" type="button" data-nav-toggle aria-expanded="false">Menu</button>
        <nav class="nav-menu" data-nav-menu aria-label="Main">
            <?php if ($role): ?>
                <?php foreach ($links[$role] as $path => $label): ?>
                    <a href="<?= $this->url($path) ?>"<?= $isCurrent($path) ? ' aria-current="page"' : '' ?>><?= e($label) ?><?php if ($path === '/customer/cart' && $cartCount > 0): ?><span class="count"><?= (int) $cartCount ?></span><?php endif; ?></a>
                <?php endforeach; ?>
                <a href="<?= $this->url('/profile') ?>"<?= $isCurrent('/profile') ? ' aria-current="page"' : '' ?>><?= e($currentUser['name']) ?></a>
                <form method="POST" action="<?= $this->url('/logout') ?>">
                    <?= $this->csrf() ?>
                    <button class="link-button" type="submit">Sign out</button>
                </form>
            <?php else: ?>
                <a href="<?= $this->url('/login') ?>">Sign in</a>
                <a href="<?= $this->url('/register') ?>">Create account</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</header>
<?php $alerts = ''; foreach ($flashes as $flash) {
    // Success notices disappear by themselves; errors stay until the next page so they can be read
    $dismiss = $flash['type'] === 'success' ? ' data-auto-dismiss="5000"' : '';
    $alerts .= '<div class="alert alert-' . e($flash['type']) . '" role="' . ($flash['type'] === 'error' ? 'alert' : 'status') . '"' . $dismiss . '>' . e($flash['message']) . '</div>';
} ?>
<?php if ($isAuthPage): ?>
<main class="auth-hero" id="content">
    <div class="container auth-grid">
        <?= $this->partial('partials/auth_intro') ?>
        <div class="auth-panel">
            <?= $alerts ?>
            <?= $content ?>
        </div>
    </div>
</main>
<?php else: ?>
<main class="container page" id="content">
    <?= $alerts ?>
    <?= $content ?>
</main>
<?php endif; ?>
<?= $this->partial('partials/footer', ['currentUser' => $currentUser]) ?>
</body>
</html>
