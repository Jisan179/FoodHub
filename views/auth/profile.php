<?php
require_once __DIR__ . '/../../controllers/auth/profile_controller.php';

$pageTitle = 'FoodHub - Manage Profile';
$currentPage = 'profile';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Profile</h1>
            <p class="page-subtitle">Update your personal information, contact info, and security preferences</p>
        </div>
        <div>
            <a href="change-password.php" class="btn btn-secondary">🔐 Change Password</a>
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

    <div class="row g-4">
        <!-- Profile Details Form Card -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">👤 Personal Details</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="username">Username</label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    class="form-control" 
                                    value="<?php echo htmlspecialchars($user['username']); ?>" 
                                    disabled
                                    style="background: #f1f5f9; cursor: not-allowed;"
                                >
                                <small style="font-size: 0.75rem; color: var(--text-muted);">Usernames cannot be changed.</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="role_badge">Assigned Role</label>
                                <div>
                                    <?php
                                    $r = normalize_role($user['role']);
                                    $badgeClass = 'badge-customer';
                                    if ($r === 'Administrator') $badgeClass = 'badge-admin';
                                    elseif ($r === 'Restaurant Manager') $badgeClass = 'badge-manager';
                                    elseif ($r === 'Rider') $badgeClass = 'badge-rider';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>" style="font-size: 0.9rem; padding: 6px 14px;"><?php echo htmlspecialchars($r); ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="name">Full Name *</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    class="form-control" 
                                    value="<?php echo htmlspecialchars($user['name']); ?>" 
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
                                    value="<?php echo htmlspecialchars($user['email']); ?>" 
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
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="status_display">Account Status</label>
                                <div>
                                    <span class="badge badge-active"><?php echo htmlspecialchars($user['status']); ?></span>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="address">Address (Delivery / Operations)</label>
                                <textarea 
                                    id="address" 
                                    name="address" 
                                    class="form-control" 
                                    rows="3"
                                    placeholder="Enter physical address details"
                                ><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div style="margin-top: 24px;">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar / Account Actions -->
        <div class="col-lg-4">
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h2 class="card-title">🛡️ Security & Login</h2>
                </div>
                <div class="card-body">
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 16px;">
                        Keep your account safe by using a strong password and updating it regularly.
                    </p>
                    <a href="change-password.php" class="btn btn-secondary" style="width: 100%; text-align: center;">Update Password</a>
                </div>
            </div>

            <div class="card" style="border: 1px solid #fecaca; background: #fffaf0;">
                <div class="card-header" style="border-bottom: 1px solid #fee2e2;">
                    <h2 class="card-title" style="color: #dc2626;">⚠️ Danger Zone</h2>
                </div>
                <div class="card-body">
                    <p style="font-size: 0.85rem; color: #991b1b; margin-bottom: 14px;">
                        Deactivating or deleting your account will end your active session and restrict portal access.
                    </p>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" onsubmit="return confirm('Are you completely sure you want to proceed? This action may be irreversible.');">
                        <input type="hidden" name="action" value="deactivate_account">
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label class="form-label" for="confirm_deactivate" style="font-size: 0.8rem; color: #991b1b;">
                                Type <strong>DELETE</strong> or <strong>DEACTIVATE</strong>:
                            </label>
                            <input 
                                type="text" 
                                id="confirm_deactivate" 
                                name="confirm_deactivate" 
                                class="form-control" 
                                placeholder="Type DELETE or DEACTIVATE" 
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-danger" style="width: 100%; font-size: 0.88rem;">
                            Close My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
