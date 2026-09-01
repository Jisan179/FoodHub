<?php
require_once __DIR__ . '/../../controllers/auth/login_controller.php';

$pageTitle = 'FoodHub - Admin Login';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
            <div class="login-header" style="text-align: center; margin-bottom: 28px;">
            <div style="display: inline-flex; flex-direction: column; align-items: flex-start; text-align: left; margin-bottom: 6px;">
                <div style="display: flex; align-items: center; font-size: 1.75rem; font-weight: 700; color: #ff5722; margin-bottom: 6px;">
                    <span style="width: 0; overflow: visible; transform: translateX(-36px); font-size: 1.5rem; line-height: 1;">🍔</span>
                    <span style="line-height: 1;">FoodHub</span>
                </div>
                <h2 style="font-size: 1.35rem; font-weight: 700; color: #1e293b; margin: 0; line-height: 1.2;">Portal Login</h2>
            </div>
            <p class="login-subtitle" style="text-align: center; margin: 0 auto;">Enter your administrative credentials to continue</p>
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
