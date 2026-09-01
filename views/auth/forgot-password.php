<?php
require_once __DIR__ . '/../../controllers/auth/forgot_password_controller.php';

$pageTitle = 'FoodHub - Reset Password';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <div class="login-card" style="max-width: 440px;">
        <div class="login-header" style="width: 100%; text-align: center; margin-bottom: 24px;">
            <a href="login.php" class="login-logo" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; margin: 0 auto 8px auto; text-decoration: none;">
                <span>🍔 FoodHub</span>
            </a>
            <h2 style="width: 100%; text-align: center; font-size: 1.45rem; font-weight: 700; margin-bottom: 4px;">Account Recovery</h2>
            <p class="login-subtitle" style="width: 100%; text-align: center; margin: 0 auto;">
                <?php echo ($step === 1) ? 'Find your account using your username or email' : 'Set a new password for your account'; ?>
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <!-- Step 1: Find Account -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" name="action" value="find_account">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="username_or_email">Username or Email Address</label>
                    <input 
                        type="text" 
                        id="username_or_email" 
                        name="username_or_email" 
                        class="form-control" 
                        placeholder="e.g. admin or customer1" 
                        value="<?php echo htmlspecialchars($username_or_email ?? ''); ?>" 
                        required 
                        autofocus
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600;">
                    Find Account
                </button>
            </form>
        <?php else: ?>
            <!-- Step 2: Set New Password -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($found_user_id ?? ''); ?>">

                <div class="alert alert-success" style="font-size: 0.88rem; padding: 10px;">
                    Account verified: <strong>@<?php echo htmlspecialchars($found_username ?? 'user'); ?></strong>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="new_password">New Password *</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-control" 
                            placeholder="Min 6 characters" 
                            required 
                            autofocus
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
                            placeholder="Re-enter password" 
                            required
                        >
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('confirm_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 1rem;">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600;">
                    Reset & Save Password
                </button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Remember your password? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Return to login</a>
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
