<?php
// manager/views/register_restaurant.php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'FoodHub - Register Restaurant';
$currentPage = 'dashboard';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Register a New Restaurant</h1>
            <p class="page-subtitle">Submit your restaurant details for admin approval.</p>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">
            ← Cancel
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 680px; margin: 0 auto;">
        <div class="card-header">
            <h2 class="card-title">Registration Form</h2>
        </div>
        <div class="card-body">
            <form action="../controllers/restaurant_controller.php?action=register" method="POST" class="form-grid" style="grid-template-columns: 1fr;">
                <div class="form-group">
                    <label class="form-label">Restaurant Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Kacchi Bhai" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address <span style="color: var(--danger);">*</span></label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Full address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+8801700000000">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Cuisine Type</label>
                    <input type="text" name="cuisine_type" class="form-control" placeholder="e.g. Traditional, Fast Food, Chinese">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Brief description of your restaurant"></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 10px;">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
