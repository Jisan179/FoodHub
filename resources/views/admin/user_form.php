<?php
$editing = $account !== null;
$title = $editing ? 'Edit user' : 'Create user';
$selectedRole = $this->rawOld('role', $account['role'] ?? 'customer');
$selectedStatus = $this->rawOld('status', $account['status'] ?? 'active');
?>
<div class="page-head"><div><h1><?= e($title) ?><?= $editing ? ': @' . e($account['username']) : '' ?></h1></div><a class="btn btn-secondary" href="<?= $this->url('/admin/users') ?>">Back to users</a></div>
<section class="card">
    <form method="POST" action="<?= $this->url($editing ? '/admin/users/' . $account['user_id'] : '/admin/users') ?>">
        <?= $this->csrf() ?>
        <div class="form-grid">
            <?php if (!$editing): ?><div class="field"><label for="username">Username</label><input id="username" name="username" value="<?= $this->old('username') ?>" maxlength="30" required></div><?php endif; ?>
            <div class="field"><label for="name">Full name</label><input id="name" name="name" value="<?= $this->old('name', $account['name'] ?? '') ?>" maxlength="100" required></div>
            <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= $this->old('email', $account['email'] ?? '') ?>" required></div>
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="<?= $this->old('phone', $account['phone'] ?? '') ?>" required></div>
            <div class="field"><label for="address">Address</label><input id="address" name="address" value="<?= $this->old('address', $account['address'] ?? '') ?>" maxlength="255"></div>
            <div class="field"><label for="role">Role</label><select id="role" name="role"><?php foreach (['admin', 'customer', 'manager', 'rider'] as $role): ?><option value="<?= $role ?>"<?= $selectedRole === $role ? ' selected' : '' ?>><?= e(status_label($role)) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="status">Status</label><select id="status" name="status"><?php foreach (['pending', 'active', 'inactive', 'suspended'] as $status): ?><option value="<?= $status ?>"<?= $selectedStatus === $status ? ' selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="password"><?= $editing ? 'New password (leave empty to keep)' : 'Password' ?></label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"<?= $editing ? '' : ' required' ?>></div>
        </div>
        <button class="btn" type="submit"><?= $editing ? 'Save changes' : 'Create user' ?></button>
    </form>
</section>
