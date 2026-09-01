<?php
require_once __DIR__ . '/../../controllers/admin/user_controller.php';

$pageTitle = 'FoodHub - Create User';
$currentPage = 'users';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$users_list_url = $in_admin_folder ? 'users.php' : 'admin/users.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Create User Account</h1>
            <p class="page-subtitle">Provision a new account for an Administrator, Restaurant Manager, Rider, or Customer</p>
        </div>
        <div>
            <a href="<?php echo $users_list_url; ?>" class="btn btn-secondary">← Back to User List</a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 780px;">
        <div class="card-header">
            <h2 class="card-title">➕ Account Details</h2>
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
                            placeholder="e.g. Sarah Connor" 
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username *</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            placeholder="e.g. sarah_c" 
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
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
                            placeholder="sarah@example.com" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            class="form-control" 
                            placeholder="+8801700000000" 
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">User Role *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="Customer">Customer</option>
                            <option value="Restaurant Manager">Restaurant Manager</option>
                            <option value="Rider">Rider</option>
                            <option value="Administrator">Administrator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Account Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="password">Password *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Min 6 characters" 
                            required
                        >
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="address">Address Details</label>
                        <textarea 
                            id="address" 
                            name="address" 
                            class="form-control" 
                            rows="2"
                            placeholder="Enter physical address details"
                        ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">Create User Account</button>
                    <a href="<?php echo $users_list_url; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
