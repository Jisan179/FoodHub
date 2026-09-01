<?php
require_once __DIR__ . '/../../controllers/auth/change_password_controller.php';

$pageTitle = 'FoodHub - Change Password';
$currentPage = 'profile';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Change Password</h1>
            <p class="page-subtitle">Update your login password to ensure your FoodHub account remains protected</p>
        </div>
        <div>
            <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
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

    <div class="card" style="max-width: 550px;">
        <div class="card-header">
            <h2 class="card-title">🔐 Password Security</h2>
        </div>
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" for="current_password">Current Password *</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-control" 
                            placeholder="Enter your current password" 
                            required 
                            autofocus
                        >
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('current_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 1rem;">👁️</button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" for="new_password">New Password *</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-control" 
                            placeholder="Min 6 characters" 
                            required
                        >
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('new_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 1rem;">👁️</button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" for="confirm_password">Confirm New Password *</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            placeholder="Re-enter your new password" 
                            required
                        >
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 1rem;">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600;">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
