<div class="form-grid">
    <div class="field"><label>Name<input name="name" value="<?= $this->old('name', $item['name'] ?? '') ?>" maxlength="150" required></label></div>
    <div class="field"><label>Category<input name="category" value="<?= $this->old('category', $item['category'] ?? 'Main Course') ?>" maxlength="50" required></label></div>
    <div class="field"><label>Price (৳)<input name="price" type="number" step="0.01" min="0.01" value="<?= $this->old('price', $item['price'] ?? '') ?>" required></label></div>
    <div class="field"><label>Image (up to 2 MB)<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label></div>
    <div class="field full"><label>Description<textarea name="description" maxlength="1000"><?= $this->old('description', $item['description'] ?? '') ?></textarea></label></div>
    <div class="field full"><label class="check"><input type="checkbox" name="is_available" value="1"<?= ($item['is_available'] ?? 1) ? ' checked' : '' ?>> Available to order</label></div>
</div>
