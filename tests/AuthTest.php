<?php

declare(strict_types=1);

test('guests are sent to sign in and roles cannot cross portals', function () {
    $guest = new Client();
    same('/FoodHub/login', $guest->get('/customer')->header('Location'));
    same('/FoodHub/login', $guest->get('/admin/users')->header('Location'));
    $customer = Client::as('customer1', 'customer123');
    foreach (['/admin', '/manager', '/rider'] as $path) {
        same(403, $customer->get($path)->status(), $path);
    }
    same(404, $customer->get('/no-such-page')->status());
});

test('every POST needs a valid CSRF token', function () {
    $client = new Client();
    same(419, $client->request('POST', '/login', [], ['login' => 'admin', 'password' => 'admin123'])->status());
    same(419, $client->request('POST', '/login', [], ['_token' => 'forged'])->status());
});

test('login rejects bad passwords and the stored hash', function () {
    $client = new Client();
    $client->post('/login', ['login' => 'admin', 'password' => 'wrong-password']);
    check(strpos($client->flash(), 'Invalid username/email or password') !== false, 'generic message');
    $client->clearFlash();
    $client->post('/login', ['login' => 'nobody', 'password' => 'wrong-password']);
    check(strpos($client->flash(), 'Invalid username/email or password') !== false, 'same message for unknown user');
    $hash = scalar("SELECT password_hash FROM users WHERE username = 'admin'");
    $client->post('/login', ['login' => 'admin', 'password' => $hash]);
    same('/FoodHub/login', $client->get('/admin')->header('Location'), 'hash is not a password');
});

test('login is rate limited even with a new session', function () {
    for ($i = 0; $i < 5; $i++) {
        (new Client())->post('/login', ['login' => 'admin', 'password' => 'guess-' . $i]);
    }
    $fresh = new Client();
    $fresh->post('/login', ['login' => 'admin', 'password' => 'admin123']);
    check(strpos($fresh->flash(), 'Too many sign-in attempts') !== false, 'sixth attempt is blocked: ' . $fresh->flash());
});

test('riders and managers wait for administrator approval', function () {
    $data = ['name' => 'New Rider', 'username' => 'newrider', 'email' => 'new@foodhub.test', 'phone' => '+8801911111111',
        'address' => 'Dhaka', 'role' => 'rider', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'];
    (new Client())->post('/register', $data);
    same('pending', scalar("SELECT status FROM users WHERE username = 'newrider'"));
    $rider = new Client();
    $rider->post('/login', ['login' => 'newrider', 'password' => 'secret-pass-1']);
    check(strpos($rider->flash(), 'waiting for administrator approval') !== false, 'pending cannot sign in');

    $id = (int) scalar("SELECT user_id FROM users WHERE username = 'newrider'");
    Client::as('admin', 'admin123')->post("/admin/users/$id/approve");
    Client::as('newrider', 'secret-pass-1');
});

test('customers are active at once and nobody can register as admin', function () {
    $base = ['name' => 'Person', 'phone' => '+8801911111112', 'address' => 'Dhaka', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'];
    (new Client())->post('/register', $base + ['username' => 'buyer', 'email' => 'buyer@foodhub.test', 'role' => 'customer']);
    same('active', scalar("SELECT status FROM users WHERE username = 'buyer'"));
    (new Client())->post('/register', $base + ['username' => 'boss', 'email' => 'boss@foodhub.test', 'role' => 'admin']);
    same(null, scalar("SELECT 1 FROM users WHERE username = 'boss'"));
});

test('password reset uses a single-use emailed link and ends old sessions', function () {
    $oldSession = Client::as('customer1', 'customer123');
    (new Client())->post('/forgot-password', ['email' => 'unknown@foodhub.test']);
    check(!is_file(Tests::$config['mail']['log_file']), 'no mail for unknown address');

    (new Client())->post('/forgot-password', ['email' => 'ibrar@foodhub.test']);
    check((bool) preg_match('#/reset-password/([a-f0-9]{64})#', (string) file_get_contents(Tests::$config['mail']['log_file']), $match), 'link emailed');
    $token = $match[1];
    same(null, scalar('SELECT 1 FROM password_resets WHERE token_hash = ?', [$token]), 'raw token is not stored');

    $visitor = new Client();
    same(200, $visitor->get("/reset-password/$token")->status());
    $visitor->post("/reset-password/$token", ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass']);
    Client::as('customer1', 'brand-new-pass');
    same('/FoodHub/login', $oldSession->get('/customer')->header('Location'), 'old session ended');
    same(302, (new Client())->get("/reset-password/$token")->status(), 'link works once');
});

test('expired reset links are refused', function () {
    (new Client())->post('/forgot-password', ['email' => 'ibrar@foodhub.test']);
    preg_match('#/reset-password/([a-f0-9]{64})#', (string) file_get_contents(Tests::$config['mail']['log_file']), $match);
    Tests::$db->run('UPDATE password_resets SET expires_at = ?', [date('Y-m-d H:i:s', time() - 60)]);
    $client = new Client();
    $client->post('/reset-password/' . $match[1], ['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass']);
    Client::as('customer1', 'customer123');
});

test('suspending an account ends its live session', function () {
    $customer = Client::as('customer1', 'customer123');
    same(200, $customer->get('/customer')->status());
    Tests::$db->run("UPDATE users SET status = 'suspended' WHERE username = 'customer1'");
    same('/FoodHub/login', $customer->get('/customer')->header('Location'));
});

test('changing the email or closing the account needs the password', function () {
    $customer = Client::as('customer2', 'customer123');
    $profile = ['name' => 'Sarah', 'email' => 'attacker@foodhub.test', 'phone' => '+8801700000004', 'address' => 'Dhaka'];
    $customer->post('/profile', $profile + ['current_password' => 'wrong']);
    same('sarah@foodhub.test', scalar("SELECT email FROM users WHERE username = 'customer2'"));
    $customer->post('/profile/deactivate', ['current_password' => 'wrong']);
    same('active', scalar("SELECT status FROM users WHERE username = 'customer2'"));
});
