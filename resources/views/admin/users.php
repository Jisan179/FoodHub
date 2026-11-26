<?php $title = 'Users'; ?>
<div class="page-head"><div><h1>Users</h1></div><a class="btn" href="<?= $this->url('/admin/users/create') ?>">Create user</a></div>
<form class="filters" method="GET" action="<?= $this->url('/admin/users') ?>">
    <input type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Name, username, email or phone" aria-label="Search users">
    <select name="role" aria-label="Role">
        <option value="">All roles</option>
        <?php foreach (['admin', 'customer', 'manager', 'rider'] as $role): ?><option value="<?= $role ?>"<?= $filters['role'] === $role ? ' selected' : '' ?>><?= e(status_label($role)) ?></option><?php endforeach; ?>
    </select>
    <select name="status" aria-label="Status">
        <option value="">All statuses</option>
        <?php foreach (['pending', 'active', 'inactive', 'suspended'] as $status): ?><option value="<?= $status ?>"<?= $filters['status'] === $status ? ' selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Filter</button>
</form>
<section class="card table-wrap">
    <table>
        <thead><tr><th>User</th><th>Contact</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$users): ?><tr><td colspan="5" class="empty">No users match these filters.</td></tr><?php endif; ?>
        <?php foreach ($users as $account): ?>
            <tr>
                <td><strong><?= e($account['name']) ?></strong><div class="muted small">@<?= e($account['username']) ?></div></td>
                <td class="small"><?= e($account['email']) ?><br><?= e($account['phone']) ?></td>
                <td><?= e(status_label($account['role'])) ?></td>
                <td><span class="<?= status_badge($account['status']) ?>"><?= e(status_label($account['status'])) ?></span></td>
                <td>
                    <div class="row">
                        <?php if ($account['status'] === 'pending'): ?>
                            <form class="inline" method="POST" action="<?= $this->url('/admin/users/' . $account['user_id'] . '/approve') ?>">
                                <?= $this->csrf() ?>
                                <button class="btn btn-sm" type="submit">Approve</button>
                            </form>
                        <?php endif; ?>
                        <a class="btn btn-secondary btn-sm" href="<?= $this->url('/admin/users/' . $account['user_id'] . '/edit') ?>">Edit</a>
                        <?php if ((int) $account['user_id'] !== (int) $currentUser['user_id']): ?>
                            <form class="inline" method="POST" action="<?= $this->url('/admin/users/' . $account['user_id'] . '/delete') ?>" data-confirm="Delete this user?">
                                <?= $this->csrf() ?>
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?= $this->partial('partials/pagination', ['page' => $page, 'path' => '/admin/users', 'query' => $filters]) ?>
