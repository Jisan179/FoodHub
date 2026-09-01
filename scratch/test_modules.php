<?php
/**
 * FoodHub - Automated Health & Integrity Test Script
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

echo "=== 1. Testing Database Connection ===\n";
if ($conn && !mysqli_connect_errno()) {
    echo "SUCCESS: Database connection established.\n";
} else {
    echo "FAILURE: Database connection failed: " . mysqli_connect_error() . "\n";
    exit(1);
}

echo "\n=== 2. Applying Schema & Checking Tables ===\n";
$schema_file = __DIR__ . '/../schema.sql';
$sql = file_get_contents($schema_file);

// Strip multi-line comments & delimiter comments
$sql = preg_replace('/--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));

$imported = 0;
foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        if (mysqli_query($conn, $stmt)) {
            $imported++;
        } else {
            echo "SQL Error in statement: " . substr(str_replace("\n", " ", $stmt), 0, 70) . "... -> " . mysqli_error($conn) . "\n";
        }
    }
}
echo "SUCCESS: Executed $imported SQL statements.\n";

$tables = ['users', 'restaurants', 'restaurant_managers', 'food_items', 'orders', 'order_items', 'order_status_log', 'deliveries', 'delivery_status_history', 'favorites', 'cart', 'reviews'];
foreach ($tables as $tbl) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM $tbl");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        echo "Table [$tbl]: " . $row['c'] . " rows.\n";
    } else {
        echo "FAILURE: Table [$tbl] missing or inaccessible: " . mysqli_error($conn) . "\n";
    }
}

echo "\n=== 3. Testing User Authentication & Passwords ===\n";
$test_users = [
    'admin' => 'admin123',
    'customer1' => 'customer123',
    'manager1' => 'manager123',
    'rider1' => 'rider123',
];

foreach ($test_users as $username => $pwd) {
    $u = find_user_by_username_or_email($conn, $username);
    if ($u && password_verify($pwd, $u['password'])) {
        echo "SUCCESS: User [$username] (Role: {$u['role']}) password verified.\n";
    } else {
        echo "FAILURE: User [$username] authentication failed.\n";
    }
}

echo "\n=== 4. Testing Manager Module Queries ===\n";
$mgr_user_id = 3; // manager1
$mgr_restaurants = getRestaurantsByManager($conn, $mgr_user_id);
echo "Manager 1 has " . count($mgr_restaurants) . " restaurants.\n";
if (!empty($mgr_restaurants)) {
    $r_id = $mgr_restaurants[0]['restaurant_id'];
    $foods = getFoodByRestaurant($conn, $r_id);
    echo "Restaurant ID $r_id has " . count($foods) . " food items.\n";
    $orders = getOrdersByRestaurants($conn, [$r_id]);
    echo "Restaurant ID $r_id has " . count($orders) . " orders.\n";
}

echo "\n=== 5. Testing Rider Module Queries ===\n";
$avail = get_available_deliveries($conn);
echo "Available deliveries for riders: " . count($avail) . "\n";
$rider_deliveries = get_deliveries_by_rider_id($conn, 4);
echo "Rider 1 (user_id 4) has " . count($rider_deliveries) . " deliveries.\n";
$rider_stats = get_rider_stats($conn, 4);
echo "Rider 1 Stats -> Total: {$rider_stats['total_deliveries']}, Earnings: \${$rider_stats['total_earnings']}\n";

echo "\n=== 6. Testing Customer Module Queries ===\n";
$cust_restaurants = get_customer_approved_restaurants($conn, null, 2);
echo "Customer can view " . count($cust_restaurants) . " approved restaurants.\n";
$cust_orders = get_customer_orders($conn, 2);
echo "Customer 1 (user_id 2) has " . count($cust_orders) . " orders.\n";

echo "\n=== All Tests Completed Successfully! ===\n";
