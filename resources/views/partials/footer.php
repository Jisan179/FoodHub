<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-about">
            <a class="footer-brand" href="<?= $this->url('/') ?>">🍔 FoodHub</a>
            <p>Order from local restaurants, follow your food from the kitchen to your door, and rate what you enjoyed.</p>
        </div>
        <nav aria-label="Company">
            <h2>Company</h2>
            <ul>
                <li><a href="<?= $this->url('/about') ?>">About FoodHub</a></li>
                <li><a href="<?= $this->url('/help') ?>">Help centre</a></li>
            </ul>
        </nav>
        <nav aria-label="Partners">
            <h2>Join us</h2>
            <ul>
                <li><a href="<?= $this->url(empty($currentUser) ? '/register' : '/help') ?>">Order as a customer</a></li>
                <li><a href="<?= $this->url('/help') ?>">Add your restaurant</a></li>
                <li><a href="<?= $this->url('/help') ?>">Ride with us</a></li>
            </ul>
        </nav>
        <nav aria-label="Legal and security">
            <h2>Legal &amp; security</h2>
            <ul>
                <li><a href="<?= $this->url('/privacy') ?>">Privacy policy</a></li>
                <li><a href="<?= $this->url('/terms') ?>">Terms of use</a></li>
                <li><a href="<?= $this->url('/security') ?>">Security</a></li>
            </ul>
        </nav>
    </div>
    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> AIUB. All rights reserved.</span>
        <span>Demonstration project · Payments are simulated</span>
    </div>
</footer>
