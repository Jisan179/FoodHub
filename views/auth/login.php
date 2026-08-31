<?php
require_once __DIR__ . '/../../controllers/auth/login_controller.php';

$pageTitle = 'FoodHub - Admin Login';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <span>🍔</span>
                <span>FoodHub</span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 4px;">Admin Portal Login</h2>
            <p class="login-subtitle">Enter your administrative credentials to continue</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    placeholder="e.g. admin" 
                    value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Enter your password" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem;">
                Sign In to Dashboard
            </button>
        </form>

        <div class="login-credentials-hint">
            <strong>Default Admin Credentials:</strong><br>
            Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
