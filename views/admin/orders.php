<?php
require_once __DIR__ . '/../../controllers/admin/order_controller.php';

$pageTitle = 'FoodHub - Order & Delivery Management';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Orders & Delivery Tracking</h1>
            <p class="page-subtitle">Track real-time orders, customer fulfillment, and assigned riders across FoodHub</p>
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

    <!-- Orders Master Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Customer Orders</h2>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Restaurant</th>
                        <th>Amount</th>
                        <th>Order Status</th>
                        <th>Delivery Status</th>
                        <th>Rider Name</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders) && count($orders) > 0): ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo $o['order_id']; ?></strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?php echo date('M d, H:i', strtotime($o['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($o['customer_name']); ?></strong><br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">📞 <?php echo htmlspecialchars($o['customer_phone'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($o['restaurant_name']); ?></strong>
                            </td>
                            <td>
                                <strong style="font-size: 1rem; color: var(--primary-dark);">
                                    ৳<?php echo number_format((float)$o['total_amount'], 2); ?>
                                </strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($o['payment_method']); ?> (<?php echo htmlspecialchars($o['payment_status']); ?>)
                                </span>
                            </td>
                            <td>
                                <?php
                                $ost = $o['order_status'];
                                $obadge = 'badge-pending';
                                if ($ost === 'Delivered') $obadge = 'badge-delivered';
                                elseif ($ost === 'Cancelled') $obadge = 'badge-cancelled';
                                elseif ($ost === 'Preparing' || $ost === 'Ready for Delivery' || $ost === 'Out for Delivery') $obadge = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $obadge; ?>"><?php echo htmlspecialchars($ost); ?></span>
                            </td>
                            <td>
                                <?php
                                $dst = $o['delivery_status'];
                                $dbadge = 'badge-pending';
                                if ($dst === 'Delivered') $dbadge = 'badge-delivered';
                                elseif ($dst === 'Cancelled') $dbadge = 'badge-cancelled';
                                elseif ($dst === 'Assigned' || $dst === 'Picked Up') $dbadge = 'badge-rider';
                                ?>
                                <span class="badge <?php echo $dbadge; ?>"><?php echo htmlspecialchars($dst); ?></span>
                            </td>
                            <td>
                                <?php if ($o['rider_name'] !== 'Unassigned'): ?>
                                    <span style="font-weight: 600; color: #1e293b;">🚴 <?php echo htmlspecialchars($o['rider_name']); ?></span><br>
                                    <span style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars($o['rider_phone'] ?? ''); ?></span>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="inline-form" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                                    <input type="hidden" name="action" value="update_order">
                                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                    
                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                        <select name="order_status" class="form-control" style="width: auto; padding: 3px 6px; font-size: 0.78rem;" title="Order Status">
                                            <option value="Pending" <?php echo ($ost === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Preparing" <?php echo ($ost === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="Ready for Delivery" <?php echo ($ost === 'Ready for Delivery') ? 'selected' : ''; ?>>Ready for Delivery</option>
                                            <option value="Out for Delivery" <?php echo ($ost === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="Delivered" <?php echo ($ost === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo ($ost === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>

                                        <select name="delivery_status" class="form-control" style="width: auto; padding: 3px 6px; font-size: 0.78rem;" title="Delivery Status">
                                            <option value="Pending Assignment" <?php echo ($dst === 'Pending Assignment') ? 'selected' : ''; ?>>Pending Assign</option>
                                            <option value="Assigned" <?php echo ($dst === 'Assigned') ? 'selected' : ''; ?>>Assigned</option>
                                            <option value="Picked Up" <?php echo ($dst === 'Picked Up') ? 'selected' : ''; ?>>Picked Up</option>
                                            <option value="Delivered" <?php echo ($dst === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo ($dst === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>

                                    <div style="display: flex; gap: 4px; width: 100%; margin-top: 2px;">
                                        <select name="rider_id" class="form-control" style="width: auto; padding: 3px 6px; font-size: 0.78rem;" title="Assign Rider">
                                            <option value="">No Rider</option>
                                            <?php foreach ($riders as $rd): ?>
                                                <option value="<?php echo $rd['user_id']; ?>" <?php echo ($o['rider_id'] == $rd['user_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($rd['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                No orders found in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
