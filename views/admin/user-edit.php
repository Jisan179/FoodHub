<?php
require_once __DIR__ . '/../../controllers/admin/user_controller.php';

$pageTitle = 'FoodHub - Edit User';
$currentPage = 'users';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$users_list_url = $in_admin_folder ? 'users.php' : 'admin/users.php';

$edit_id = intval($_GET['id'] ?? 0);
$edit_user = find_user_by_id($conn, $edit_id);

if (!$edit_user) {
    echo "<div class='main-wrapper'><div class='alert alert-error'>User account #{$edit_id} not found. <a href='{$users_list_url}'>Return to list</a></div></div>";
    require_once __DIR__ . '/../partials/footer.php';
    exit();
}
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit User #<?php echo $edit_user['user_id']; ?></h1>
            <p class="page-subtitle">Modify profile details, reassign role, change status, or reset credentials for @<?php echo htmlspecialchars($edit_user['username']); ?></p>
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
            <h2 class="card-title">✏️ Edit Profile: @<?php echo htmlspecialchars($edit_user['username']); ?></h2>
        </div>
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo $edit_user['user_id']; ?>" method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($edit_user['username']); ?>" 
                            disabled
                            style="background: #f1f5f9; cursor: not-allowed;"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($edit_user['name']); ?>" 
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
                            value="<?php echo htmlspecialchars($edit_user['email']); ?>" 
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
                            value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">User Role *</label>
                        <select id="role" name="role" class="form-control" required>
                            <?php $curr_role = normalize_role($edit_user['role']); ?>
                            <option value="Customer" <?php echo ($curr_role === 'Customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="Restaurant Manager" <?php echo ($curr_role === 'Restaurant Manager') ? 'selected' : ''; ?>>Restaurant Manager</option>
                            <option value="Rider" <?php echo ($curr_role === 'Rider') ? 'selected' : ''; ?>>Rider</option>
                            <option value="Administrator" <?php echo ($curr_role === 'Administrator') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Account Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Active" <?php echo ($edit_user['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($edit_user['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="Suspended" <?php echo ($edit_user['status'] === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="new_password">Reset Password (leave blank to retain current)</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-control" 
                            placeholder="Enter new password if resetting"
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
                        ><?php echo htmlspecialchars($edit_user['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">Save User Changes</button>
                    <a href="<?php echo $users_list_url; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
