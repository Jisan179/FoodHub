<?php
// manager/views/menu.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../models/RestaurantModel.php';
require_once __DIR__ . '/../models/FoodModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Restaurant Manager') {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$restaurants = getRestaurantsByManager($conn, $user_id);
$restaurant_id = intval($_GET['restaurant_id'] ?? ($restaurants[0]['restaurant_id'] ?? 0));

$restaurant = getRestaurantByIdAndManager($conn, $restaurant_id, $user_id);

if (!$restaurant) {
    $_SESSION['error'] = "Restaurant not found or unauthorized.";
    header('Location: dashboard.php');
    exit();
}

$food_items = getFoodByRestaurant($conn, $restaurant_id);

$pageTitle = 'FoodHub - Menu Management';
$currentPage = 'menu';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Menu: <?php echo htmlspecialchars($restaurant['name']); ?></h1>
            <p class="page-subtitle">Manage food items, availability, and pricing.</p>
        </div>
        
        <button onclick="document.getElementById('add-modal').style.display='flex'" class="btn btn-primary">
            ➕ Add New Food Item
        </button>
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

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Food Items</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ITEM NAME</th>
                        <th>CATEGORY</th>
                        <th>PRICE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($food_items)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No food items found for this restaurant.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($food_items as $item): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td style="font-weight: 700; color: var(--primary);">৳<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $item['status'] === 'Available' ? 'badge-approved' : 'badge-danger'; ?>">
                                        <?php echo htmlspecialchars($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="btn btn-sm btn-secondary">
                                            Edit
                                        </button>
                                        <form action="../controllers/menu_controller.php?action=delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id; ?>">
                                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div id="add-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center;">
    <div class="card" style="width: 90%; max-width: 500px; padding: 24px; position: relative;">
        <span onclick="document.getElementById('add-modal').style.display='none'" style="position:absolute; top:16px; right:16px; font-size:1.5rem; cursor:pointer;">&times;</span>
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">Add Menu Item</h2>
        <form action="../controllers/menu_controller.php?action=add" method="POST" class="form-grid" style="grid-template-columns: 1fr;">
            <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id; ?>">
            
            <div class="form-group">
                <label class="form-label">Item Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Price (৳)</label>
                <input type="number" step="0.01" name="price" class="form-control" required min="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" placeholder="Main Course, Appetizer, etc.">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Add Item</button>
        </form>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="edit-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center;">
    <div class="card" style="width: 90%; max-width: 500px; padding: 24px; position: relative;">
        <span onclick="document.getElementById('edit-modal').style.display='none'" style="position:absolute; top:16px; right:16px; font-size:1.5rem; cursor:pointer;">&times;</span>
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">Edit Menu Item</h2>
        <form action="../controllers/menu_controller.php?action=edit" method="POST" class="form-grid" style="grid-template-columns: 1fr;">
            <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id; ?>">
            <input type="hidden" name="item_id" id="edit_item_id">
            
            <div class="form-group">
                <label class="form-label">Item Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Price (৳)</label>
                <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required min="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" id="edit_category" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" id="edit_status" class="form-control">
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Save Changes</button>
        </form>
    </div>
</div>

<script src="../../assets/js/manager.js"></script>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
