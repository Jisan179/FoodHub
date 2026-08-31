<?php
require_once __DIR__ . '/../../controllers/admin/user_controller.php';

$pageTitle = 'FoodHub - User Management';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$current_page_url = $in_admin_folder ? 'users.php' : 'admin/users.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Create new users, search across profiles, and manage system accounts</p>
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

    <!-- Top Section: User Creation Form -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">➕ Create New User Account</h2>
        </div>
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control" 
                            placeholder="e.g. Jisan Ahmmed Jim" 
                            value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username *</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            placeholder="e.g. jisan_jim" 
                            value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="e.g. jisan@example.com" 
                            value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Assign account password" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">User Role *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="Customer" <?php echo (($role ?? '') === 'Customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="Restaurant Manager" <?php echo (($role ?? '') === 'Restaurant Manager') ? 'selected' : ''; ?>>Restaurant Manager</option>
                            <option value="Rider" <?php echo (($role ?? '') === 'Rider') ? 'selected' : ''; ?>>Rider</option>
                            <option value="Admin" <?php echo (($role ?? '') === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            class="form-control" 
                            placeholder="e.g. +8801700000009" 
                            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="address">Residential / Work Address</label>
                        <textarea 
                            id="address" 
                            name="address" 
                            class="form-control" 
                            placeholder="Enter physical address details"
                        ><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Create User Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Section: Search & Users Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Registered Users</h2>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by name, user, email..." 
                    value="<?php echo htmlspecialchars($search_query ?? ''); ?>"
                >
                <button type="submit" class="btn btn-secondary">Search</button>
                <?php if (!empty($search_query)): ?>
                    <a href="<?php echo $current_page_url; ?>" class="btn btn-secondary" style="background:#e2e8f0; color:#475569;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name & Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined</th>
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
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <?php
                                $r = $u['role'];
                                $badgeClass = 'badge-customer';
                                if ($r === 'Admin') $badgeClass = 'badge-admin';
                                elseif ($r === 'Restaurant Manager') $badgeClass = 'badge-manager';
                                elseif ($r === 'Rider') $badgeClass = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($r); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($u['phone'] ?? '—'); ?></td>
                            <td style="max-width: 220px; font-size: 0.85rem;"><?php echo htmlspecialchars($u['address'] ?? '—'); ?></td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['user_id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                    <a 
                                        href="<?php echo $current_page_url; ?>?delete_id=<?php echo $u['user_id']; ?>" 
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete user #<?php echo $u['user_id']; ?> (<?php echo htmlspecialchars($u['username']); ?>)?');"
                                    >
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">(Current)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No users found matching your query.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
