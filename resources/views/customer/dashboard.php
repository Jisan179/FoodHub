<?php $title = 'Home'; ?>
<section class="hero">
    <div class="hero-text">
        <h1>Welcome back, <?= e($currentUser['name']) ?>! 👋</h1>
        <p>Craving something delicious? Explore partner restaurants, order your favorite meals and track your food.</p>
        <form class="hero-search" method="GET" action="<?= $this->url('/customer/restaurants') ?>">
            <input type="search" name="search" placeholder="Search biryani, burgers or restaurants..." aria-label="Search">
            <button class="btn" type="submit">Find Food</button>
        </form>
    </div>
    <div class="hero-art" aria-hidden="true"><span>🍕</span><span>🍔</span><span>🍛</span><span>🍜</span></div>
</section>
<div class="grid grid-stats">
    <div class="card stat"><span>Orders</span><strong><?= $stats['total'] ?></strong></div>
    <div class="card stat"><span>In progress</span><strong><?= $stats['active'] ?></strong></div>
    <div class="card stat"><span>Favorites</span><strong><?= $favoriteCount ?></strong></div>
    <div class="card stat"><span>Spent on delivered orders</span><strong><?= money($stats['spent']) ?></strong></div>
</div>
<?php if ($pendingReviews > 0): ?>
    <div class="alert alert-warning">You have <?= $pendingReviews ?> item(s) waiting for a review. <a href="<?= $this->url('/customer/reviews') ?>">Review now</a></div>
<?php endif; ?>
<?php if ($activeOrders): ?>
    <section class="card">
        <h2>Orders in progress</h2>
        <div class="table-wrap"><table><tbody>
        <?php foreach ($activeOrders as $order): ?>
            <tr>
                <td><a href="<?= $this->url('/customer/orders/' . $order['order_id']) ?>">Order #<?= (int) $order['order_id'] ?></a></td>
                <td><?= e($order['restaurant_name']) ?></td>
                <td><span class="<?= status_badge($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></td>
                <td class="right"><?= money($order['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </section>
<?php endif; ?>
<section class="section">
    <div class="row-between"><h2>Restaurants</h2><a href="<?= $this->url('/customer/restaurants') ?>">See all</a></div>
    <div class="grid grid-cards">
        <?php foreach ($restaurants as $restaurant): ?>
            <?= $this->partial('partials/restaurant_card', ['restaurant' => $restaurant]) ?>
        <?php endforeach; ?>
    </div>
</section>
