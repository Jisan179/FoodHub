<?php

declare(strict_types=1);

/** Customer 1 orders two biryani from restaurant 1. */
function place_test_order(Client $customer): int
{
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '2']);
    $response = $customer->post('/customer/checkout', ['delivery_address' => 'Test address', 'contact_phone' => '+8801700000001', 'payment_method' => 'cod']);
    check((bool) preg_match('#/customer/orders/(\d+)$#', $response->header('Location'), $match), 'order placed: ' . $customer->flash());

    return (int) $match[1];
}

function delivery_of(int $orderId): array
{
    return row('SELECT * FROM deliveries WHERE order_id = ?', [$orderId]);
}

test('the full order lifecycle works from cart to review', function () {
    $customer = Client::as('customer1', 'customer123');
    $manager = Client::as('manager1', 'manager123');
    $rider = Client::as('rider1', 'rider123');

    $id = place_test_order($customer);
    $order = row('SELECT * FROM orders WHERE order_id = ?', [$id]);
    same('490.00', $order['total_amount'], '2 x 220 + 50');
    same(0, (int) scalar('SELECT COUNT(*) FROM cart_items WHERE customer_id = 2'), 'cart emptied');

    $delivery = (int) delivery_of($id)['delivery_id'];
    $rider->post("/rider/deliveries/$delivery/accept");
    same('assigned', delivery_of($id)['status']);
    $rider->post("/rider/deliveries/$delivery/pickup");
    same('assigned', delivery_of($id)['status'], 'no pickup before the food is ready');

    $manager->post("/manager/orders/$id/status", ['status' => 'preparing']);
    $manager->post("/manager/orders/$id/status", ['status' => 'ready']);
    $rider->post("/rider/deliveries/$delivery/pickup");
    same('out_for_delivery', scalar('SELECT status FROM orders WHERE order_id = ?', [$id]));
    $rider->post("/rider/deliveries/$delivery/complete");

    same('delivered', scalar('SELECT status FROM orders WHERE order_id = ?', [$id]));
    same('paid', scalar('SELECT payment_status FROM orders WHERE order_id = ?', [$id]));
    same('49.00', delivery_of($id)['rider_earning']);
    same(['placed', 'rider_assigned', 'preparing', 'ready', 'out_for_delivery', 'delivered'],
        array_column(Tests::$db->all('SELECT event FROM order_status_history WHERE order_id = ? ORDER BY history_id', [$id]), 'event'));

    $review = ['order_id' => (string) $id, 'item_id' => '1', 'rating' => '5', 'comment' => '<script>alert(1)</script>'];
    $customer->post('/customer/reviews', $review);
    $customer->post('/customer/reviews', $review);
    same(1, (int) scalar('SELECT COUNT(*) FROM reviews WHERE order_id = ?', [$id]), 'one review per item');
    $page = $customer->get('/customer/reviews')->body();
    check(strpos($page, '<script>alert(1)</script>') === false && strpos($page, '&lt;script&gt;') !== false, 'review is escaped');
});

test('prices always come from the database', function () {
    $customer = Client::as('customer1', 'customer123');
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '1', 'price' => '1']);
    $customer->post('/customer/checkout', ['delivery_address' => 'A', 'contact_phone' => '+8801700000001', 'payment_method' => 'cod', 'total_amount' => '1']);
    same('270.00', scalar('SELECT total_amount FROM orders ORDER BY order_id DESC LIMIT 1'));
});

test('a cart holds one restaurant and asks before replacing it', function () {
    $customer = Client::as('customer1', 'customer123');
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '1']);
    $customer->post('/customer/cart', ['item_id' => '4', 'quantity' => '1']);
    same(1, (int) scalar('SELECT item_id FROM cart_items WHERE customer_id = 2'));
    check(strpos($customer->get('/customer/restaurants/2')->body(), 'Empty my cart') !== false, 'confirmation shown');
    $customer->post('/customer/cart', ['item_id' => '4', 'quantity' => '1', 'replace' => '1']);
    same(4, (int) scalar('SELECT item_id FROM cart_items WHERE customer_id = 2'));
});

test('quantities are capped', function () {
    $customer = Client::as('customer1', 'customer123');
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '9999']);
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '40']);
    same(50, (int) scalar('SELECT quantity FROM cart_items WHERE customer_id = 2'));
});

test('managers can close a restaurant, which stops cart and checkout', function () {
    $customer = Client::as('customer1', 'customer123');
    $manager = Client::as('manager1', 'manager123');
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '1']);
    check(strpos($manager->get('/manager')->body(), 'Close for orders') !== false, 'button exists');
    $manager->post('/manager/restaurants/1/toggle');
    same(0, (int) scalar('SELECT is_open FROM restaurants WHERE restaurant_id = 1'));

    $before = (int) scalar('SELECT COUNT(*) FROM orders');
    $customer->post('/customer/checkout', ['delivery_address' => 'A', 'contact_phone' => '+8801700000001', 'payment_method' => 'cod']);
    same($before, (int) scalar('SELECT COUNT(*) FROM orders'), 'checkout blocked');
    $customer->post('/customer/cart', ['item_id' => '2', 'quantity' => '1']);
    same(null, scalar('SELECT 1 FROM cart_items WHERE item_id = 2'), 'cannot add');
});

test('deleted dishes vanish from menus and carts but stay in old orders', function () {
    $customer = Client::as('customer1', 'customer123');
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '1']);
    Client::as('manager1', 'manager123')->post('/manager/items/1/delete');
    same(0, (int) scalar('SELECT COUNT(*) FROM cart_items WHERE item_id = 1'));
    $customer->clearFlash(); // the "Added to cart" message names the dish
    check(strpos($customer->get('/customer/restaurants/1')->body(), 'Chicken Dum Biryani') === false, 'hidden from menu');
    $customer->post('/customer/cart', ['item_id' => '1', 'quantity' => '1']);
    same(0, (int) scalar('SELECT COUNT(*) FROM cart_items WHERE item_id = 1'), 'cannot add again');
    same(1, (int) scalar('SELECT COUNT(*) FROM order_items WHERE item_id = 1'), 'history kept');
});

test('customers, managers and riders cannot touch records they do not own', function () {
    $id = place_test_order(Client::as('customer1', 'customer123'));
    $delivery = (int) delivery_of($id)['delivery_id'];

    $otherCustomer = Client::as('customer2', 'customer123');
    same(404, $otherCustomer->get("/customer/orders/$id")->status());
    same(404, $otherCustomer->post("/customer/orders/$id/cancel")->status());

    $otherManager = Client::as('manager2', 'manager123');
    same(404, $otherManager->get("/manager/orders/$id")->status());
    same(404, $otherManager->post("/manager/orders/$id/status", ['status' => 'cancelled'])->status());
    same(404, $otherManager->post('/manager/restaurants/1/toggle')->status());
    same(404, $otherManager->get('/manager/items/1/edit')->status());
    same('pending', scalar('SELECT status FROM orders WHERE order_id = ?', [$id]));

    Client::as('rider1', 'rider123')->post("/rider/deliveries/$delivery/accept");
    $otherRider = Client::as('rider2', 'rider123');
    foreach (['accept', 'pickup', 'complete'] as $action) {
        $otherRider->post("/rider/deliveries/$delivery/$action");
    }
    $otherRider->post("/rider/deliveries/$delivery/release", ['reason' => 'x']);
    same(4, (int) delivery_of($id)['rider_id']);
    same('assigned', delivery_of($id)['status']);
});

test('a released trip returns to the queue and keeps the order alive', function () {
    $id = place_test_order(Client::as('customer1', 'customer123'));
    $delivery = (int) delivery_of($id)['delivery_id'];
    $manager = Client::as('manager1', 'manager123');
    $manager->post("/manager/orders/$id/status", ['status' => 'preparing']);
    $manager->post("/manager/orders/$id/status", ['status' => 'ready']);
    $rider = Client::as('rider1', 'rider123');
    $rider->post("/rider/deliveries/$delivery/accept");
    $rider->post("/rider/deliveries/$delivery/pickup");
    check(strpos($rider->get('/rider')->body(), 'Release delivery') !== false, 'button exists');
    $rider->post("/rider/deliveries/$delivery/release", ['reason' => 'Bike broke down']);
    same('waiting', delivery_of($id)['status']);
    same(null, delivery_of($id)['rider_id']);
    same('ready', scalar('SELECT status FROM orders WHERE order_id = ?', [$id]));
    Client::as('rider2', 'rider123')->post("/rider/deliveries/$delivery/accept");
    same(7, (int) delivery_of($id)['rider_id']);
});

test('rider requests hide customer contact details until accepted', function () {
    $id = place_test_order(Client::as('customer1', 'customer123'));
    $rider = Client::as('rider1', 'rider123');
    check(strpos($rider->get('/rider')->body(), 'Test address') === false, 'address hidden');
    $rider->post('/rider/deliveries/' . delivery_of($id)['delivery_id'] . '/accept');
    check(strpos($rider->get('/rider')->body(), 'Test address') !== false, 'address visible to assigned rider');
});

test('cancellation follows the rules and cancels the delivery too', function () {
    $customer = Client::as('customer1', 'customer123');
    $manager = Client::as('manager1', 'manager123');
    $first = place_test_order($customer);
    $customer->post("/customer/orders/$first/cancel");
    same('cancelled', delivery_of($first)['status']);

    $second = place_test_order($customer);
    $manager->post("/manager/orders/$second/status", ['status' => 'preparing']);
    $manager->post("/manager/orders/$second/status", ['status' => 'ready']);
    $customer->post("/customer/orders/$second/cancel");
    same('ready', scalar('SELECT status FROM orders WHERE order_id = ?', [$second]));
});

test('administrators can assign riders and only active ones', function () {
    $id = place_test_order(Client::as('customer1', 'customer123'));
    $admin = Client::as('admin', 'admin123');
    $admin->post("/admin/orders/$id/rider", ['rider_id' => '2']);
    same(null, delivery_of($id)['rider_id'], 'a customer is not a rider');
    $admin->post("/admin/orders/$id/rider", ['rider_id' => '7']);
    same(7, (int) delivery_of($id)['rider_id']);
    $admin->post("/admin/orders/$id/rider", ['rider_id' => '0']);
    same('waiting', delivery_of($id)['status']);
});
