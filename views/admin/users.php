<?php
require_once __DIR__ . '/../../controllers/admin/user_controller.php';

$pageTitle = 'FoodHub - User Management';
$currentPage = 'users';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$current_page_url = $in_admin_folder ? 'users.php' : 'admin/users.php';
$create_url = $in_admin_folder ? 'user-create.php' : 'admin/user-create.php';
$edit_base_url = $in_admin_folder ? 'user-edit.php' : 'admin/user-edit.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Create accounts for any role, search & filter profiles, and manage system permissions</p>
        </div>
        <div>
            <a href="<?php echo $create_url; ?>" class="btn btn-primary">➕ Create New User</a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <span>✅</span>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <!-- Role Summary Statistics Cards -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card" style="--card-accent: #3b82f6;">
            <div class="stat-title">Total Users</div>
            <div class="stat-value"><?php echo number_format($role_counts['total']); ?></div>
            <div class="stat-desc">All registered accounts</div>
        </div>

        <div class="stat-card" style="--card-accent: #ff4757;">
            <div class="stat-title">Administrators</div>
            <div class="stat-value"><?php echo number_format($role_counts['Administrator'] ?? 0); ?></div>
            <div class="stat-desc">System admins</div>
        </div>

        <div class="stat-card" style="--card-accent: #10b981;">
            <div class="stat-title">Customers</div>
            <div class="stat-value"><?php echo number_format($role_counts['Customer'] ?? 0); ?></div>
            <div class="stat-desc">Ordering clients</div>
        </div>

        <div class="stat-card" style="--card-accent: #f59e0b;">
            <div class="stat-title">Managers & Riders</div>
            <div class="stat-value">
                <?php echo number_format(($role_counts['Restaurant Manager'] ?? 0) + ($role_counts['Rider'] ?? 0)); ?>
            </div>
            <div class="stat-desc">
                <?php echo $role_counts['Restaurant Manager'] ?? 0; ?> Managers, <?php echo $role_counts['Rider'] ?? 0; ?> Riders
            </div>
        </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 18px 24px;">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3 align-items-center">
                
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px;">Live Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Search name, username, email, phone..." 
                        value="<?php echo htmlspecialchars($search_query); ?>"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px;">Filter by Role</label>
                    <select name="role" class="form-control" onchange="this.form.submit()">
                        <option value="All" <?php echo ($role_filter === 'All') ? 'selected' : ''; ?>>All Roles</option>
                        <option value="Administrator" <?php echo ($role_filter === 'Administrator') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="Customer" <?php echo ($role_filter === 'Customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="Restaurant Manager" <?php echo ($role_filter === 'Restaurant Manager') ? 'selected' : ''; ?>>Restaurant Manager</option>
                        <option value="Rider" <?php echo ($role_filter === 'Rider') ? 'selected' : ''; ?>>Rider</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px;">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="All" <?php echo ($status_filter === 'All') ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Active" <?php echo ($status_filter === 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($status_filter === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Suspended" <?php echo ($status_filter === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>

                <div class="col-md-3" style="display: flex; gap: 8px; align-items: flex-end; margin-top: auto;">
                    <button type="submit" class="btn btn-secondary" style="flex: 1;">Filter</button>
                    <?php if (!empty($search_query) || $role_filter !== 'All' || $status_filter !== 'All'): ?>
                        <a href="<?php echo $current_page_url; ?>" class="btn btn-secondary" style="background: #e2e8f0; color: #475569;">Reset</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">All Users (<?php echo $total_filtered; ?> found)</h2>
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Page <strong><?php echo $page; ?></strong> of <strong><?php echo $total_pages; ?></strong>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Profile</th>
                        <th>Email & Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Address</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users) && count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?php echo $u['user_id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($u['name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">@<?php echo htmlspecialchars($u['username']); ?></span>
                            </td>
                            <td>
                                <span><?php echo htmlspecialchars($u['email']); ?></span><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($u['phone'] ?? '—'); ?></span>
                            </td>
                            <td>
                                <?php
                                $r = normalize_role($u['role']);
                                $badgeClass = 'badge-customer';
                                if ($r === 'Administrator') $badgeClass = 'badge-admin';
                                elseif ($r === 'Restaurant Manager') $badgeClass = 'badge-manager';
                                elseif ($r === 'Rider') $badgeClass = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($r); ?></span>
                            </td>
                            <td>
                                <?php
                                $st = $u['status'] ?? 'Active';
                                $stClass = 'badge-active';
                                if ($st === 'Inactive') $stClass = 'badge-inactive';
                                elseif ($st === 'Suspended') $stClass = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $stClass; ?>"><?php echo htmlspecialchars($st); ?></span>
                            </td>
                            <td style="max-width: 180px; font-size: 0.82rem;"><?php echo htmlspecialchars($u['address'] ?? '—'); ?></td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <a 
                                        href="<?php echo $edit_base_url; ?>?id=<?php echo $u['user_id']; ?>" 
                                        class="btn btn-secondary btn-sm"
                                        title="Edit user details"
                                    >
                                        Edit
                                    </a>

                                    <?php if ($u['user_id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                        <a 
                                            href="<?php echo $current_page_url; ?>?delete_id=<?php echo $u['user_id']; ?>" 
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete user #<?php echo $u['user_id']; ?> (<?php echo htmlspecialchars($u['username']); ?>)?');"
                                            title="Delete user"
                                        >
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; padding: 4px 6px; background: #f1f5f9; border-radius: 4px;">(You)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 35px;">
                                No user accounts found matching your search and filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div class="card-body" style="border-top: 1px solid var(--border-color); display: flex; justify-content: center; gap: 8px; padding: 16px;">
            <?php 
            $query_params = $_GET;
            ?>
            <?php if ($page > 1): ?>
                <?php $query_params['page'] = $page - 1; ?>
                <a href="<?php echo $current_page_url . '?' . http_build_query($query_params); ?>" class="btn btn-secondary btn-sm">« Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php $query_params['page'] = $i; ?>
                <a 
                    href="<?php echo $current_page_url . '?' . http_build_query($query_params); ?>" 
                    class="btn btn-sm <?php echo ($i === $page) ? 'btn-primary' : 'btn-secondary'; ?>"
                >
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <?php $query_params['page'] = $page + 1; ?>
                <a href="<?php echo $current_page_url . '?' . http_build_query($query_params); ?>" class="btn btn-secondary btn-sm">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
