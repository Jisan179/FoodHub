<?php

declare(strict_types=1);

test('rejecting a restaurant needs a reason and editing resubmits it', function () {
    $manager = Client::as('manager1', 'manager123');
    $manager->post('/manager/restaurants', ['name' => 'Tea House', 'cuisine_type' => 'Cafe', 'address' => 'Dhaka', 'phone' => '+8801912345678', 'description' => '']);
    $id = (int) scalar("SELECT restaurant_id FROM restaurants WHERE name = 'Tea House'");
    same('pending', scalar('SELECT status FROM restaurants WHERE restaurant_id = ?', [$id]));

    $admin = Client::as('admin', 'admin123');
    $admin->post("/admin/restaurants/$id/status", ['status' => 'rejected', 'reason' => '']);
    same('pending', scalar('SELECT status FROM restaurants WHERE restaurant_id = ?', [$id]));
    $admin->post("/admin/restaurants/$id/status", ['status' => 'rejected', 'reason' => 'Address is incomplete']);
    check(strpos($manager->get('/manager')->body(), 'Address is incomplete') !== false, 'manager sees the reason');
    same(404, Client::as('customer1', 'customer123')->get("/customer/restaurants/$id")->status(), 'hidden from customers');

    $manager->post("/manager/restaurants/$id", ['name' => 'Tea House', 'cuisine_type' => 'Cafe', 'address' => 'House 1, Dhaka', 'phone' => '+8801912345678']);
    same('pending', scalar('SELECT status FROM restaurants WHERE restaurant_id = ?', [$id]));
});

test('records with order history cannot be deleted', function () {
    $admin = Client::as('admin', 'admin123');
    foreach ([2, 3, 4] as $userId) { // customer, owner and rider with history
        $admin->post("/admin/users/$userId/delete");
        check(scalar('SELECT 1 FROM users WHERE user_id = ?', [$userId]) !== null, "user $userId kept");
    }
    Client::as('manager1', 'manager123')->post('/manager/restaurants/1/delete');
    check(scalar('SELECT 1 FROM restaurants WHERE restaurant_id = 1') !== null, 'restaurant kept');
    $admin->post('/admin/users/7/delete');
    same(null, scalar('SELECT 1 FROM users WHERE user_id = 7'), 'unused rider can be deleted');
});

test('the administrator cannot lock themselves out', function () {
    $admin = Client::as('admin', 'admin123');
    $admin->post('/admin/users/1', ['name' => 'A', 'email' => 'admin@foodhub.test', 'phone' => '+8801700000000', 'role' => 'customer', 'status' => 'active']);
    same('admin', scalar('SELECT role FROM users WHERE user_id = 1'));
    $admin->post('/admin/users/1/delete');
    $admin->post('/profile/deactivate', ['current_password' => 'admin123']);
    same('active', scalar('SELECT status FROM users WHERE user_id = 1'));
});

test('search treats wildcards and quotes as plain text', function () {
    $customer = Client::as('customer1', 'customer123');
    foreach (["%", "_", "' OR 1=1 --"] as $term) {
        $body = $customer->get('/customer/restaurants', ['search' => $term])->body();
        check(strpos($body, 'No restaurants match') !== false, "term $term matches nothing");
    }
    check(strpos($customer->get('/customer/restaurants', ['search' => 'burger'])->body(), 'The Burger Spot') !== false, 'normal search works');
});

test('redirects never leave the application', function () {
    $customer = Client::as('customer1', 'customer123');
    foreach (['https://evil.example/x', 'http://localhost.evil.example/FoodHub/', 'http://localhost/other-app/'] as $referer) {
        $location = $customer->post('/customer/restaurants/2/favorite', [], ['HTTP_REFERER' => $referer])->header('Location');
        same('/FoodHub/customer/favorites', $location, $referer);
    }
    $location = $customer->post('/customer/restaurants/2/favorite', [], ['HTTP_REFERER' => 'http://localhost/FoodHub/customer/restaurants?search=a'])->header('Location');
    same('/FoodHub/customer/restaurants?search=a', $location);
});

test('every page renders for its role with security headers and no inline code', function () {
    $pages = [
        'guest' => ['/login', '/register', '/forgot-password', '/about', '/help', '/privacy', '/terms', '/security'],
        'customer1' => ['/customer', '/customer/restaurants', '/customer/restaurants/1', '/customer/favorites', '/customer/cart', '/customer/orders', '/customer/orders/1', '/customer/reviews', '/profile'],
        'manager2' => ['/manager', '/manager/orders', '/manager/orders/1', '/manager/restaurants/create', '/manager/restaurants/2/edit', '/manager/restaurants/2/menu', '/manager/items/4/edit', '/profile'],
        'rider1' => ['/rider', '/rider/history', '/profile'],
        'admin' => ['/admin', '/admin/users', '/admin/users/create', '/admin/users/2/edit', '/admin/restaurants', '/admin/orders', '/admin/orders/2', '/profile'],
    ];
    $passwords = ['customer1' => 'customer123', 'manager2' => 'manager123', 'rider1' => 'rider123', 'admin' => 'admin123'];
    $notices = [];
    set_error_handler(function (int $level, string $message, string $file, int $line) use (&$notices) {
        $notices[] = "$message at $file:$line";
        return true;
    });
    try {
        foreach ($pages as $who => $paths) {
            $client = $who === 'guest' ? new Client() : Client::as($who, $passwords[$who]);
            if ($who === 'customer1') {
                $client->post('/customer/cart', ['item_id' => '1', 'quantity' => '1']);
                $paths[] = '/customer/checkout';
            }
            foreach ($paths as $path) {
                $response = $client->get($path);
                same(200, $response->status(), "$who $path");
                check(!preg_match('/\sstyle=|\son[a-z]+=|<script(?![^>]*\ssrc=)/i', $response->body()), "inline code on $path");
                check(strpos($response->header('Content-Security-Policy'), "script-src 'self'") !== false, "CSP on $path");
            }
        }
    } finally {
        restore_error_handler();
    }
    same([], $notices, 'no PHP warnings or notices');
});

test('every footer link opens, signed in or not', function () {
    foreach ([new Client(), Client::as('customer1', 'customer123')] as $client) {
        $body = $client->get('/about')->body();
        check(strpos($body, 'AIUB. All rights reserved.') !== false, 'copyright shown');
        preg_match_all('#<footer.*?</footer>#s', $body, $footer);
        preg_match_all('#href="/FoodHub(/[a-z\-]*)"#', $footer[0][0], $links);
        foreach (array_unique($links[1]) as $path) {
            check(in_array($client->get($path)->status(), [200, 302], true), "footer link $path");
        }
    }
    check(strpos((new Client())->get('/login')->body(), 'class="auth-hero"') !== false, 'sign-in has the photo layout');
    check(strpos((new Client())->get('/login')->body(), 'data-nav-menu') === false, 'no duplicate header links beside the form');
    check(strpos((new Client())->get('/about')->body(), 'data-nav-menu') !== false, 'information pages keep the header links');
    check(strpos(Client::as('customer1', 'customer123')->get('/security')->body(), 'class="auth-hero"') === false, 'other pages do not');
});

test('success notices are marked to disappear, errors stay', function () {
    $customer = Client::as('customer1', 'customer123');
    $customer->post('/logout');
    $page = $customer->get('/login')->body();
    check((bool) preg_match('/class="alert alert-success"[^>]*data-auto-dismiss="5000"[^>]*>You have been signed out/', $page), 'sign-out notice leaves by itself');
    $customer->post('/login', ['login' => 'customer1', 'password' => 'wrong-password']);
    $page = $customer->get('/login')->body();
    check(strpos($page, 'alert-error') !== false && strpos($page, 'data-auto-dismiss') === false, 'errors stay');
});

test('each page has its own browser title', function () {
    check(strpos((new Client())->get('/login')->body(), '<title>Sign in · FoodHub</title>') !== false, 'login title');
    $customer = Client::as('customer1', 'customer123');
    check(strpos($customer->get('/customer/restaurants/1')->body(), '<title>Spice Grill House · FoodHub</title>') !== false, 'menu title');
    check(strpos($customer->get('/customer')->body(), '<title>Home · FoodHub</title>') !== false, 'title survives partials');
    check(strpos($customer->get('/admin')->body(), '<title>Access denied · FoodHub</title>') !== false, 'error title');
});

test('reviews use clickable stars and riders see a route map', function () {
    $customer = Client::as('customer1', 'customer123');
    Tests::$db->run('DELETE FROM reviews');
    $page = $customer->get('/customer/reviews')->body();
    same(10, substr_count($page, 'type="radio"'), 'five stars for each of the two dishes waiting');
    check(strpos($page, '<select name="rating"') === false, 'no drop-down');
    $customer->post('/customer/reviews', ['order_id' => '1', 'item_id' => '4', 'rating' => '4', 'comment' => '']);
    same(4, (int) scalar('SELECT rating FROM reviews WHERE item_id = 4'));
    check(strpos($customer->get('/customer/reviews')->body(), '4 out of 5 stars') !== false, 'stars displayed');

    $rider = Client::as('rider1', 'rider123');
    $rider->post('/rider/deliveries/2/accept');
    $body = $rider->get('/rider')->body();
    check(strpos($body, 'class="route-map"') !== false, 'map shown for an accepted trip');
    check(strpos($body, 'google.com/maps/dir/') !== false, 'directions link');
});

test('the order trend ignores cancelled orders and other restaurants', function () {
    $store = [];
    $orders = new App\Models\OrderRepository(new App\Core\App(Tests::$config, new App\Core\Session($store)));
    $report = $orders->trendByDay(6, 7);
    same(7, count($report));
    same(480.0, array_sum(array_column($report, 'value')), 'only restaurant 2');
    same(2, array_sum(array_column($report, 'items')));
    Tests::$db->run("UPDATE orders SET status = 'cancelled' WHERE order_id = 1");
    same(0, array_sum(array_column($orders->trendByDay(6, 7), 'orders')), 'cancelled orders do not count');
    same([], $orders->trendingItems(6, date('Y-m-d 00:00:00')));
});

test('ordering two burgers puts them first in today\'s trend at once, and cancelling takes them out', function () {
    $migrator = new App\Core\Migrator(Tests::$config['db']);
    $migrator->addDemoSales(50, 10);
    $store = [];
    $orders = new App\Models\OrderRepository(new App\Core\App(Tests::$config, new App\Core\Session($store)));
    $midnight = date('Y-m-d 00:00:00');
    $today = date('Y-m-d');
    $before = $orders->trendByDay(6, 7)[$today];
    same('Loaded Truffle Fries', $orders->trendingItems(6, $midnight)[0]['item_name'], 'fries lead before the order');

    $customer = Client::as('customer1', 'customer123');
    $customer->post('/customer/cart', ['item_id' => '4', 'quantity' => '2']);
    $location = $customer->post('/customer/checkout', ['delivery_address' => 'A', 'contact_phone' => '+8801700000001', 'payment_method' => 'cod'])->header('Location');

    $top = $orders->trendingItems(6, $midnight)[0];
    same('Double Cheeseburger', $top['item_name'], 'burger moves to number one');
    $after = $orders->trendByDay(6, 7)[$today];
    same($before['orders'] + 1, $after['orders']);
    same($before['items'] + 2, $after['items']);
    same($before['value'] + 640.0, $after['value']);
    $page = Client::as('manager2', 'manager123')->get('/manager')->body();
    $panel = substr($page, strpos($page, 'id="trending-today"'), 900);
    check(strpos($panel, 'Double Cheeseburger') !== false && strpos($panel, 'Double Cheeseburger') < strpos($panel, 'Loaded Truffle Fries'), 'dashboard shows it first');

    preg_match('#(\d+)$#', $location, $match);
    $customer->post('/customer/orders/' . $match[1] . '/cancel');
    same($before, $orders->trendByDay(6, 7)[$today], 'cancelling restores the figures');
});

test('the trend explains an empty week instead of showing blank bars', function () {
    Tests::$db->run("UPDATE orders SET status = 'cancelled'");
    $page = Client::as('manager1', 'manager123')->get('/manager')->body();
    check(strpos($page, 'No orders in the last 7 days') !== false, 'empty message');
    check(strpos($page, '<progress') === false, 'no blank bars');
});

test('sample sales fill the week once and leave demo accounts clean', function () {
    $migrator = new App\Core\Migrator(Tests::$config['db']);
    $before = (int) scalar('SELECT COUNT(*) FROM orders WHERE customer_id IN (2, 5)');
    same(36, $migrator->addDemoSales(50, 10));
    same(0, $migrator->addDemoSales(50, 10), 'running again adds nothing');
    same($before, (int) scalar('SELECT COUNT(*) FROM orders WHERE customer_id IN (2, 5)'));
    same(0, (int) scalar('SELECT COUNT(*) FROM deliveries WHERE delivered_at > ?', [date('Y-m-d H:i:s', time() + 1)]), 'nothing in the future');
    $store = [];
    $report = (new App\Models\OrderRepository(new App\Core\App(Tests::$config, new App\Core\Session($store))))->trendByDay(3, 7);
    same(0, count(array_filter($report, static fn (array $day) => $day['orders'] === 0)), 'every day has orders');
});

test('user list filters and paginates', function () {
    $admin = Client::as('admin', 'admin123');
    for ($i = 0; $i < 12; $i++) {
        Tests::$db->insert('users', ['name' => "Extra $i", 'username' => "extra$i", 'email' => "extra$i@foodhub.test", 'password_hash' => 'x']);
    }
    $body = $admin->get('/admin/users', ['search' => 'extra'])->body();
    check(strpos($body, 'Page 1 of 2 (12 total)') !== false, 'paginated');
    check(strpos($admin->get('/admin/users', ['search' => 'extra', 'page' => '2'])->body(), 'Page 2 of 2') !== false, 'second page');
});
