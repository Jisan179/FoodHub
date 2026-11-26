<?php

declare(strict_types=1);

use App\Core\Database;

/**
 * Sample sales for the last seven days, so reports and histories have something to show.
 * All of it is made up. It belongs to a separate sample customer who cannot sign in,
 * so the demo customer accounts stay clean. Running it again adds nothing.
 *
 * @return int number of sample orders added
 */
return static function (Database $db, float $deliveryFee, int $commissionPercent): int {
    if ($db->value("SELECT 1 FROM users WHERE username = 'sample_customer'")) {
        return 0;
    }
    $customer = $db->insert('users', [
        'name' => 'Sample Customer', 'username' => 'sample_customer', 'email' => 'sample.customer@foodhub.test',
        'role' => 'customer', 'status' => 'active', 'phone' => '+8801700000099', 'address' => 'Sample address, Dhaka',
        // A random password that is never shown or stored anywhere else
        'password_hash' => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
    ]);
    $riders = array_map('intval', array_column($db->all("SELECT user_id FROM users WHERE role = 'rider' AND status = 'active' ORDER BY user_id"), 'user_id'));

    // Orders per day for each demo restaurant, from six days ago until today
    // Today is deliberately light, so that real orders visibly change today's ranking
    $plan = [1 => [2, 3, 2, 4, 3, 5, 1], 2 => [1, 2, 3, 2, 4, 3, 1]];
    $added = 0;
    foreach ($plan as $restaurantId => $days) {
        $menu = $db->all('SELECT item_id, name, price FROM food_items WHERE restaurant_id = ? AND deleted_at IS NULL ORDER BY item_id', [$restaurantId]);
        if (!$menu || !$riders) {
            continue;
        }
        foreach ($days as $index => $count) {
            $daysAgo = count($days) - 1 - $index;
            for ($n = 0; $n < $count; $n++) {
                // Today's deliveries must lie in the past, even just after midnight
                $delivered = $daysAgo === 0
                    ? max(strtotime('today') + $n, time() - ($n + 1) * 420)
                    : strtotime("-$daysAgo days 13:00") + $n * 5400;
                $placed = $delivered - 2400;

                $first = $menu[($index + $n) % count($menu)];
                $lines = [[$first, 1 + ($n % 2)]];
                if ($n % 3 === 0 && count($menu) > 1) {
                    $lines[] = [$menu[($index + $n + 1) % count($menu)], 1];
                }
                if ($daysAgo === 0) {
                    $lines = [[$menu[count($menu) - 1], 1]];
                }
                $subtotal = 0.0;
                foreach ($lines as [$item, $quantity]) {
                    $subtotal += (float) $item['price'] * $quantity;
                }
                $total = $subtotal + $deliveryFee;
                $rider = $riders[($index + $n) % count($riders)];

                $orderId = $db->insert('orders', [
                    'customer_id' => $customer, 'restaurant_id' => $restaurantId,
                    'subtotal' => (string) $subtotal, 'delivery_fee' => (string) $deliveryFee, 'total_amount' => (string) $total,
                    'status' => 'delivered', 'payment_status' => 'paid', 'delivery_address' => 'Sample address, Dhaka',
                    'contact_phone' => '+8801700000099', 'created_at' => date('Y-m-d H:i:s', $placed),
                ]);
                foreach ($lines as [$item, $quantity]) {
                    $db->insert('order_items', [
                        'order_id' => $orderId, 'item_id' => (int) $item['item_id'], 'item_name' => $item['name'],
                        'unit_price' => $item['price'], 'quantity' => $quantity,
                    ]);
                }
                $db->insert('deliveries', [
                    'order_id' => $orderId, 'rider_id' => $rider, 'status' => 'delivered',
                    'assigned_at' => date('Y-m-d H:i:s', $placed + 300), 'picked_up_at' => date('Y-m-d H:i:s', $placed + 1500),
                    'delivered_at' => date('Y-m-d H:i:s', $delivered),
                    'rider_earning' => (string) round($total * $commissionPercent / 100, 2),
                ]);
                foreach (['placed' => $placed, 'delivered' => $delivered] as $event => $time) {
                    $db->insert('order_status_history', [
                        'order_id' => $orderId, 'event' => $event, 'note' => 'Sample data', 'created_at' => date('Y-m-d H:i:s', $time),
                    ]);
                }
                $added++;
            }
        }
    }

    return $added;
};
