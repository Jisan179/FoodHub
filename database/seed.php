<?php

declare(strict_types=1);

use App\Core\Database;

/** Demo data for local development and tests. Never load this on a public server. */
return static function (Database $db): void {
    $users = [
        // id, name, username, email, role, status, phone, address, password
        [1, 'System Administrator', 'admin', 'admin@foodhub.test', 'admin', 'active', '+8801700000000', 'Gulshan 2, Dhaka', 'admin123'],
        [2, 'Ibrar Amin', 'customer1', 'ibrar@foodhub.test', 'customer', 'active', '+8801700000001', 'House 12, Road 5, Dhanmondi, Dhaka', 'customer123'],
        [3, 'Rahim Ahmed', 'manager1', 'rahim@foodhub.test', 'manager', 'active', '+8801700000002', 'Road 11, Gulshan 1, Dhaka', 'manager123'],
        [4, 'Karim Khan', 'rider1', 'karim@foodhub.test', 'rider', 'active', '+8801700000003', 'Sector 4, Uttara, Dhaka', 'rider123'],
        [5, 'Sarah Jenkins', 'customer2', 'sarah@foodhub.test', 'customer', 'active', '+8801700000004', 'Block C, Bashundhara R/A, Dhaka', 'customer123'],
        [6, 'Tariqul Islam', 'manager2', 'tariq@foodhub.test', 'manager', 'active', '+8801700000005', 'Sector 3, Uttara, Dhaka', 'manager123'],
        [7, 'Fahim Hasan', 'rider2', 'fahim@foodhub.test', 'rider', 'active', '+8801700000006', 'Road 10, Banani, Dhaka', 'rider123'],
    ];
    foreach ($users as [$id, $name, $username, $email, $role, $status, $phone, $address, $password]) {
        $db->insert('users', [
            'user_id' => $id, 'name' => $name, 'username' => $username, 'email' => $email, 'role' => $role,
            'status' => $status, 'phone' => $phone, 'address' => $address,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    $restaurants = [
        [1, 3, 'Spice Grill House', 'South Asian grills, kebabs and slow-cooked biryani.', 'Plot 45, Road 11, Gulshan 1, Dhaka', '+8801811111111', 'Grill and Biryani'],
        [2, 6, 'The Burger Spot', 'Smash burgers, seasoned fries and thick shakes.', 'House 22, Sector 3, Uttara, Dhaka', '+8801822222222', 'Burgers and Fast Food'],
    ];
    foreach ($restaurants as [$id, $owner, $name, $description, $address, $phone, $cuisine]) {
        $db->insert('restaurants', [
            'restaurant_id' => $id, 'owner_id' => $owner, 'name' => $name, 'description' => $description,
            'address' => $address, 'phone' => $phone, 'cuisine_type' => $cuisine, 'status' => 'approved',
        ]);
    }

    $food = [
        [1, 1, 'Chicken Dum Biryani', 'Basmati rice cooked with marinated chicken and spices.', 220, 'Main Course'],
        [2, 1, 'Beef Seekh Kebab Wrap', 'Charcoal-grilled beef seekh in butter naan with mint chutney.', 180, 'Appetizer'],
        [3, 1, 'Mutton Kacchi Biryani', 'Wedding-style kacchi with tender mutton and spiced potato.', 380, 'Main Course'],
        [4, 2, 'Double Cheeseburger', 'Two beef smash patties with cheddar, pickles and house sauce.', 320, 'Burgers'],
        [5, 2, 'Loaded Truffle Fries', 'Crispy fries with parmesan and truffle mayo.', 160, 'Sides'],
    ];
    foreach ($food as [$id, $restaurant, $name, $description, $price, $category]) {
        $db->insert('food_items', [
            'item_id' => $id, 'restaurant_id' => $restaurant, 'name' => $name,
            'description' => $description, 'price' => $price, 'category' => $category,
        ]);
    }

    // One finished order (for reviews and reports) and one waiting for the kitchen
    $orders = [
        [1, 2, 2, 480, 'delivered', 'paid', 'delivered', 4, [[4, 'Double Cheeseburger', 320, 1], [5, 'Loaded Truffle Fries', 160, 1]]],
        [2, 5, 1, 400, 'pending', 'unpaid', 'waiting', null, [[1, 'Chicken Dum Biryani', 220, 1], [2, 'Beef Seekh Kebab Wrap', 180, 1]]],
    ];
    foreach ($orders as [$id, $customer, $restaurant, $subtotal, $status, $payment, $deliveryStatus, $rider, $items]) {
        $db->insert('orders', [
            'order_id' => $id, 'customer_id' => $customer, 'restaurant_id' => $restaurant,
            'subtotal' => $subtotal, 'delivery_fee' => 50, 'total_amount' => $subtotal + 50,
            'status' => $status, 'payment_status' => $payment,
            'delivery_address' => $users[$customer - 1][7], 'contact_phone' => $users[$customer - 1][6],
        ]);
        foreach ($items as [$item, $name, $price, $quantity]) {
            $db->insert('order_items', ['order_id' => $id, 'item_id' => $item, 'item_name' => $name, 'unit_price' => $price, 'quantity' => $quantity]);
        }
        $db->insert('order_status_history', ['order_id' => $id, 'event' => 'placed', 'changed_by' => $customer]);
        $db->insert('deliveries', [
            'order_id' => $id, 'rider_id' => $rider, 'status' => $deliveryStatus,
            'delivered_at' => $deliveryStatus === 'delivered' ? date('Y-m-d H:i:s') : null,
            'rider_earning' => $deliveryStatus === 'delivered' ? round(($subtotal + 50) * 0.10, 2) : 0,
        ]);
    }

    $db->insert('reviews', ['customer_id' => 2, 'order_id' => 1, 'item_id' => 4, 'rating' => 5, 'comment' => 'Juicy and full of flavor.']);
    $db->insert('favorites', ['customer_id' => 2, 'restaurant_id' => 1]);
};
