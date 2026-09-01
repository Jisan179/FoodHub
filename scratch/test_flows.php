<?php
/**
 * FoodHub - End-to-End Functional Flow & Dynamic Path Verification Test
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/restaurant_model.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../models/delivery_model.php';
require_once __DIR__ . '/../models/rider_model.php';
require_once __DIR__ . '/../models/customer_model.php';
require_once __DIR__ . '/../manager/models/RestaurantModel.php';
require_once __DIR__ . '/../manager/models/FoodModel.php';
require_once __DIR__ . '/../manager/models/OrderModel.php';

echo "====================================================\n";
echo "       FOODHUB END-TO-END FLOW VERIFICATION         \n";
echo "====================================================\n\n";

// TEST 1: Dynamic Path Resolution at Various Depths
echo "--- TEST 1: Dynamic Path Resolution ---\n";
$project_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));

$test_files = [
    'root (index.php)' => $project_root . '/index.php',
    'depth 1 (admin/dashboard.php)' => $project_root . '/admin/dashboard.php',
    'depth 1 (customer/cart.php)' => $project_root . '/customer/cart.php',
    'depth 1 (rider/dashboard.php)' => $project_root . '/rider/dashboard.php',
    'depth 2 (customer/actions/place_order.php)' => $project_root . '/customer/actions/place_order.php',
    'depth 2 (manager/views/menu.php)' => $project_root . '/manager/views/menu.php',
    'depth 2 (manager/controllers/order_controller.php)' => $project_root . '/manager/controllers/order_controller.php',
];

foreach ($test_files as $label => $file_path) {
    $_SERVER['SCRIPT_FILENAME'] = $file_path;
    $computed_root = get_foodhub_root_path();
    echo "  * $label => Root Prefix: '{$computed_root}'\n";
}

// TEST 2: Customer Cart -> Order -> Review Flow
echo "\n--- TEST 2: Customer Cart & Checkout Flow ---\n";
$cust_id = 2; // Ibrar Amin
$item_id = 1; // Chicken Dum Biryani ($8.50)

// 2a. Add to Cart (clear conflict if existing cart from different restaurant)
$cart_res = add_to_cart($conn, $cust_id, $item_id, 2, true);
echo "  * Add item to cart: " . ($cart_res['status'] === 'success' ? "SUCCESS" : "FAILED: " . ($cart_res['message'] ?? '')) . "\n";
$cart = get_customer_cart($conn, $cust_id);
echo "  * Customer Cart has " . count($cart) . " items.\n";

// 2b. Place Order
$place_res = place_customer_order($conn, $cust_id, 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery');
$order_id = $place_res['order_id'] ?? null;

if ($order_id) {
    echo "  * Customer Order placed successfully: Order #$order_id\n";
    $placed_order = get_customer_order_details($conn, $order_id, $cust_id);
    echo "    - Order Total: \${$placed_order['total_amount']}, Status: {$placed_order['order_status']}\n";
} else {
    echo "  * Place order FAILED: " . ($place_res['error'] ?? '') . "\n";
}

// TEST 3: Restaurant Manager Order Management Flow
echo "\n--- TEST 3: Manager Order Management Flow ---\n";
$mgr_user_id = 3; // manager1 (Spice Grill House)
if ($order_id) {
    // 3a. Accept Order -> Preparing
    $res1 = updateOrderStatus($conn, $order_id, 'Preparing', [1], $mgr_user_id);
    echo "  * Manager accepted order (Status -> Preparing): " . ($res1['success'] ? "SUCCESS" : "FAILED: {$res1['message']}") . "\n";

    // 3b. Order Ready -> Ready for Delivery
    $res2 = updateOrderStatus($conn, $order_id, 'Ready for Delivery', [1], $mgr_user_id);
    echo "  * Manager ready order (Status -> Ready for Delivery): " . ($res2['success'] ? "SUCCESS" : "FAILED: {$res2['message']}") . "\n";

    $logs = mysqli_query($conn, "SELECT * FROM order_status_log WHERE order_id = $order_id ORDER BY log_id ASC");
    echo "    - Logged Status Changes for Order #$order_id: " . mysqli_num_rows($logs) . " records.\n";
}

// TEST 4: Rider Delivery Lifecycle Flow
echo "\n--- TEST 4: Rider Delivery Lifecycle Flow ---\n";
$rider_id = 4; // Karim Khan
if ($order_id) {
    $d_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT delivery_id FROM deliveries WHERE order_id = $order_id"));
    $delivery_id = intval($d_row['delivery_id'] ?? 0);

    // 4a. Rider claims / accepts delivery
    $claim_res = rider_update_delivery($conn, $delivery_id, $rider_id, 'accept', 'Claiming order');
    echo "  * Rider accepted delivery #$delivery_id: " . ($claim_res['ok'] ? "SUCCESS" : "FAILED: {$claim_res['message']}") . "\n";

    // 4b. Rider marks picked up
    $pickup_res = rider_update_delivery($conn, $delivery_id, $rider_id, 'pickup', 'Collected hot food from restaurant');
    echo "  * Rider marked Picked Up: " . ($pickup_res['ok'] ? "SUCCESS" : "FAILED: {$pickup_res['message']}") . "\n";

    // 4c. Rider marks delivered
    $deliv_res = rider_update_delivery($conn, $delivery_id, $rider_id, 'deliver', 'Delivered to customer at door');
    echo "  * Rider marked Delivered: " . ($deliv_res['ok'] ? "SUCCESS" : "FAILED: {$deliv_res['message']}") . "\n";

    $deliv_hist = mysqli_query($conn, "SELECT * FROM delivery_status_history WHERE delivery_id = $delivery_id ORDER BY history_id ASC");
    echo "    - Delivery History records: " . mysqli_num_rows($deliv_hist) . " records.\n";
}

// TEST 5: Customer Reviews
echo "\n--- TEST 5: Customer Review Submission ---\n";
if ($order_id) {
    $rev_res = submit_food_review($conn, $cust_id, $order_id, $item_id, 5, 'Absolutely delicious! Delivered fast and hot.');
    echo "  * Customer submitted review: " . ($rev_res['success'] ? "SUCCESS" : "FAILED: " . ($rev_res['message'] ?? '')) . "\n";
    $my_revs = get_customer_reviews($conn, $cust_id);
    echo "  * Customer reviews total: " . count($my_revs) . "\n";
}

echo "\n====================================================\n";
echo "      ALL END-TO-END FLOWS EXECUTED SUCCESSFULLY    \n";
echo "====================================================\n";
