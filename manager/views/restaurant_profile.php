<?php
// manager/views/restaurant_profile.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/RestaurantModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$restaurant_id = intval($_GET['id'] ?? 0);

$restaurant = getRestaurantByIdAndManager($conn, $restaurant_id, $user_id);

if (!$restaurant) {
    $_SESSION['error'] = "Restaurant not found or unauthorized.";
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'FoodHub - Edit Restaurant Profile';
$currentPage = 'dashboard';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Restaurant Profile</h1>
            <p class="page-subtitle">Update details for <?php echo htmlspecialchars($restaurant['name']); ?>.</p>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span>✅</span>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 680px; margin: 0 auto;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Restaurant Details</h2>
            <form action="../controllers/restaurant_controller.php?action=delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this restaurant? This action cannot be undone.');">
                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['restaurant_id']; ?>">
                <button type="submit" class="btn btn-sm btn-danger">
                    Delete Restaurant
                </button>
            </form>
        </div>
        
        <div class="card-body">
            <form action="../controllers/restaurant_controller.php?action=update" method="POST" class="form-grid" style="grid-template-columns: 1fr;">
                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['restaurant_id']; ?>">
                
                <div class="form-group">
                    <label class="form-label">Restaurant Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($restaurant['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address <span style="color: var(--danger);">*</span></label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($restaurant['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($restaurant['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Cuisine Type</label>
                    <input type="text" name="cuisine_type" class="form-control" value="<?php echo htmlspecialchars($restaurant['cuisine_type'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($restaurant['description']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 10px;">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
