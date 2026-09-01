<?php
require_once __DIR__ . '/../../controllers/auth/login_controller.php';

$pageTitle = 'FoodHub - Sign In';
require_once __DIR__ . '/../partials/header.php';

$remembered_user = $_COOKIE['foodhub_user'] ?? '';
?>

<div class="login-container">
    <div class="login-card" style="max-width: 440px;">
        <div class="login-header" style="width: 100%; text-align: center; margin-bottom: 28px;">
            <div class="login-logo" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; margin: 0 auto 8px auto;">
                <span>🍔 FoodHub</span>
            </div>
            <h2 style="width: 100%; text-align: center; font-size: 1.45rem; font-weight: 700; margin-bottom: 4px;">Welcome Back</h2>
            <p class="login-subtitle" style="width: 100%; text-align: center; margin: 0 auto;">Sign in with your credentials to access your portal</p>
        </div>

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

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="loginForm">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" for="username">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    placeholder="Enter username or email" 
                    value="<?php echo htmlspecialchars(!empty($username) ? $username : $remembered_user); ?>" 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
                    <a href="forgot-password.php" style="font-size: 0.82rem; color: var(--primary); text-decoration: none; font-weight: 500;">Forgot password?</a>
                </div>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password" 
                        required
                    >
                    <button type="button" class="btn-toggle-pass" onclick="togglePassword('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 1rem;">👁️</button>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: var(--text-muted); cursor: pointer;">
                    <input type="checkbox" name="remember_me" value="1" <?php echo !empty($remembered_user) ? 'checked' : ''; ?>>
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 600;">
                Sign In to Dashboard
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Register here</a>
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
