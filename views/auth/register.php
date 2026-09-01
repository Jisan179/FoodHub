<?php
require_once __DIR__ . '/../../controllers/auth/register_controller.php';

$pageTitle = 'FoodHub - Create Account';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="login-container">
    <div class="login-card" style="max-width: 540px;">
        <div class="login-header" style="width: 100%; text-align: center; margin-bottom: 24px;">
            <a href="login.php" class="login-logo" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; margin: 0 auto 8px auto; text-decoration: none;">
                <span>🍔 FoodHub</span>
            </a>
            <h2 style="width: 100%; text-align: center; font-size: 1.45rem; font-weight: 700; margin-bottom: 4px;">Join FoodHub</h2>
            <p class="login-subtitle" style="width: 100%; text-align: center; margin: 0 auto;">Create your account to start ordering, managing, or delivering food</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="registerForm">
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="name">Full Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control" 
                    placeholder="e.g. John Doe" 
                    value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                    required 
                    autofocus
                >
            </div>

            <div class="row g-2" style="margin-bottom: 14px;">
                <div class="col-md-6">
                    <label class="form-label" for="username">Username *</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="e.g. johndoe" 
                        value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="john@example.com" 
                        value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                        required
                    >
                </div>
            </div>

            <div class="row g-2" style="margin-bottom: 14px;">
                <div class="col-md-6">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        class="form-control" 
                        placeholder="+8801700000000" 
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="role">Register As *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="Customer" <?php echo (($role ?? '') === 'Customer') ? 'selected' : ''; ?>>Customer (Order Food)</option>
                        <option value="Restaurant Manager" <?php echo (($role ?? '') === 'Restaurant Manager') ? 'selected' : ''; ?>>Restaurant Manager</option>
                        <option value="Rider" <?php echo (($role ?? '') === 'Rider') ? 'selected' : ''; ?>>Delivery Rider</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="address">Delivery / Operational Address</label>
                <input 
                    type="text" 
                    id="address" 
                    name="address" 
                    class="form-control" 
                    placeholder="House, Road, Area, City" 
                    value="<?php echo htmlspecialchars($address ?? ''); ?>"
                >
            </div>

            <div class="row g-2" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <label class="form-label" for="password">Password *</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Min 6 characters" 
                            required
                        >
                        <button type="button" class="btn-toggle-pass" onclick="togglePassword('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 1rem;">👁️</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
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
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 600;">
                Create My Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Log in here</a>
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
