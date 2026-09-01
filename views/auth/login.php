<?php
require_once __DIR__ . '/../../controllers/auth/login_controller.php';

$pageTitle = 'FoodHub - Rider Login';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <span>🍔</span>
                <span>FoodHub</span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 4px;">Rider Portal Login</h2>
            <p class="login-subtitle">Sign in with your rider account</p>
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
                    placeholder="e.g. rider1" 
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
                Sign In
            </button>
        </form>

        <div class="login-credentials-hint">
            <strong>Demo rider account:</strong><br>
            Rider: <code>rider1 / rider123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
