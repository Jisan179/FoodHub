<?php
require_once __DIR__ . '/../../controllers/auth/login_controller.php';

$pageTitle = 'FoodHub - Admin Login';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header" style="width: 100%; text-align: center; margin-bottom: 28px;">
            <div class="login-logo" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; margin: 0 auto 8px auto;">
                <span>🍔</span>
                <span>FoodHub</span>
            </div>
            <h2 style="width: 100%; text-align: center; font-size: 1.35rem; font-weight: 700; margin-bottom: 4px;">Portal Login</h2>
            <p class="login-subtitle" style="width: 100%; text-align: center; margin: 0 auto;">Enter your administrative credentials to continue</p>
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
                    placeholder="Enter your username" 
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

    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
