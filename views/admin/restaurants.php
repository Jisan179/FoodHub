<?php
require_once __DIR__ . '/../../controllers/admin/restaurant_controller.php';

$pageTitle = 'FoodHub - Restaurant Management';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Restaurant Approvals & Management</h1>
            <p class="page-subtitle">Review merchant applications, verify restaurant profiles, and update operational statuses</p>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Restaurant Table Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Partner Restaurants</h2>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Restaurant</th>
                        <th>Owner / Manager</th>
                        <th>Phone & Address</th>
                        <th>Menu Items</th>
                        <th>Current Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($restaurants) && count($restaurants) > 0): ?>
                        <?php foreach ($restaurants as $r): ?>
                        <tr>
                            <td><strong>#<?php echo $r['restaurant_id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($r['restaurant_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($r['description'] ?? 'No description provided'); ?></span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($r['owner_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($r['owner_email']); ?></span>
                            </td>
                            <td>
                                <span>📞 <?php echo htmlspecialchars($r['phone']); ?></span><br>
                                <span style="font-size: 0.82rem; color: var(--text-muted);">📍 <?php echo htmlspecialchars($r['address']); ?></span>
                            </td>
                            <td>
                                <span class="badge" style="background: #e2e8f0; color: #334155;">
                                    🍽️ <?php echo $r['total_items']; ?> items
                                </span>
                            </td>
                            <td>
                                <?php
                                $status = $r['restaurant_status'];
                                $badgeClass = 'badge-pending';
                                if ($status === 'Approved') $badgeClass = 'badge-approved';
                                elseif ($status === 'Rejected') $badgeClass = 'badge-rejected';
                                elseif ($status === 'Suspended') $badgeClass = 'badge-suspended';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="restaurant_id" value="<?php echo $r['restaurant_id']; ?>">
                                    
                                    <select name="status" class="form-control" style="width: auto; padding: 4px 8px; font-size: 0.82rem;">
                                        <option value="Pending" <?php echo ($status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo ($status === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo ($status === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="Suspended" <?php echo ($status === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No restaurants currently registered in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
