<?php $title = 'Restaurants'; ?>
<div class="page-head"><div><h1>Restaurants and food</h1><p>Search restaurants by name, or dishes by name or category.</p></div></div>
<form class="filters" method="GET" action="<?= $this->url('/customer/restaurants') ?>">
    <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search" aria-label="Search">
    <select name="cuisine" aria-label="Cuisine">
        <option value="">All cuisines</option>
        <?php foreach ($cuisines as $option): ?>
            <option value="<?= e($option) ?>"<?= $cuisine === $option ? ' selected' : '' ?>><?= e($option) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Apply</button>
    <?php if ($search !== '' || $cuisine !== ''): ?><a class="btn btn-secondary" href="<?= $this->url('/customer/restaurants') ?>">Reset</a><?php endif; ?>
</form>
<?php if ($foodResults): ?>
    <section class="card">
        <h2>Dishes matching "<?= e($search) ?>"</h2>
        <div class="table-wrap"><table><tbody>
        <?php foreach ($foodResults as $item): ?>
            <tr>
                <td><div class="dish"><?= $this->partial('partials/thumb', ['path' => $item['image_path'], 'name' => $item['name']]) ?><div><strong><?= e($item['name']) ?></strong><div class="muted small"><?= e($item['category']) ?></div></div></div></td>
                <td><a href="<?= $this->url('/customer/restaurants/' . $item['restaurant_id']) ?>"><?= e($item['restaurant_name']) ?></a><?php if (!$item['is_open']): ?> <span class="badge badge-danger">Closed</span><?php endif; ?></td>
                <td class="right"><?= money($item['price']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </section>
<?php endif; ?>
<section class="section">
    <h2>Restaurants (<?= count($restaurants) ?>)</h2>
    <?php if (!$restaurants): ?><p class="card empty"><?= $foodResults ? 'No restaurant name matches. See the matching dishes above.' : 'No restaurants match your search.' ?></p><?php endif; ?>
    <div class="grid grid-cards">
        <?php foreach ($restaurants as $restaurant): ?>
            <?= $this->partial('partials/restaurant_card', ['restaurant' => $restaurant]) ?>
        <?php endforeach; ?>
    </div>
</section>
