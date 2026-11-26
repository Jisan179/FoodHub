<?php
$editing = $restaurant !== null;
$title = $editing ? 'Edit restaurant' : 'Register restaurant';
$action = $editing ? '/manager/restaurants/' . $restaurant['restaurant_id'] : '/manager/restaurants';
?>
<div class="page-head"><div><h1><?= e($title) ?></h1><?php if (!$editing): ?><p>New restaurants are reviewed by an administrator.</p><?php endif; ?></div></div>
<?php if ($editing && $restaurant['status'] === 'rejected'): ?>
    <div class="alert alert-error">Rejected: <?= e($restaurant['rejection_reason']) ?>. Saving changes sends it back for review.</div>
<?php endif; ?>
<section class="card">
    <form method="POST" action="<?= $this->url($action) ?>" enctype="multipart/form-data">
        <?= $this->csrf() ?>
        <div class="form-grid">
            <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= $this->old('name', $restaurant['name'] ?? '') ?>" maxlength="150" required></div>
            <div class="field"><label for="cuisine_type">Cuisine</label><input id="cuisine_type" name="cuisine_type" value="<?= $this->old('cuisine_type', $restaurant['cuisine_type'] ?? '') ?>" maxlength="100" required></div>
            <div class="field"><label for="address">Address</label><input id="address" name="address" value="<?= $this->old('address', $restaurant['address'] ?? '') ?>" maxlength="255" required></div>
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="<?= $this->old('phone', $restaurant['phone'] ?? '') ?>" required></div>
            <div class="field full"><label for="description">Description</label><textarea id="description" name="description" maxlength="1000"><?= $this->old('description', $restaurant['description'] ?? '') ?></textarea></div>
            <div class="field full">
                <label for="image">Cover image (JPG, PNG or WebP, up to 2 MB)</label>
                <?php if (!empty($restaurant['image_path'])): ?><img class="preview" src="<?= $this->asset($restaurant['image_path']) ?>" alt="Current cover image"><?php endif; ?>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>
        <button class="btn" type="submit"><?= $editing ? 'Save changes' : 'Submit for approval' ?></button>
    </form>
</section>
<?php if ($editing): ?>
    <section class="card section">
        <h2>Delete restaurant</h2>
        <p class="small muted">A restaurant with order history cannot be deleted. Close it for orders from the dashboard instead.</p>
        <form method="POST" action="<?= $this->url($action . '/delete') ?>" data-confirm="Delete this restaurant and its menu?">
            <?= $this->csrf() ?>
            <button class="btn btn-danger" type="submit">Delete restaurant</button>
        </form>
    </section>
<?php endif; ?>
