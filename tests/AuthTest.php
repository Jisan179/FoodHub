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

test('public sign-up only ever creates active customers', function () {
    check(strpos((new Client())->get('/register')->body(), 'name="role"') === false, 'the form offers no role choice');

    $base = ['name' => 'Person', 'phone' => '+8801911111112', 'address' => 'Dhaka', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'];
    $staff = (int) scalar("SELECT COUNT(*) FROM users WHERE role <> 'customer'");
    // A hand-made request cannot choose a role or a status
    foreach (['none' => [], 'manager' => ['role' => 'manager'], 'rider' => ['role' => 'rider'], 'admin' => ['role' => 'admin', 'status' => 'active']] as $name => $extra) {
        (new Client())->post('/register', $base + $extra + ['username' => "join_$name", 'email' => "join_$name@foodhub.test"]);
        same('customer', scalar('SELECT role FROM users WHERE username = ?', ["join_$name"]), "role for $name");
        same('active', scalar('SELECT status FROM users WHERE username = ?', ["join_$name"]), "status for $name");
    }
    same($staff, (int) scalar("SELECT COUNT(*) FROM users WHERE role <> 'customer'"), 'no staff account appeared');
    same(403, Client::as('join_manager', 'secret-pass-1')->get('/manager')->status());
});

test('only an administrator creates manager and rider accounts', function () {
    $account = static fn (string $username, string $role, string $status = 'active') => [
        'username' => $username, 'name' => 'Staff Person', 'email' => "$username@foodhub.test", 'phone' => '+8801911111113',
        'address' => 'Dhaka', 'role' => $role, 'status' => $status, 'password' => 'secret-pass-1',
    ];

    (new Client())->post('/admin/users', $account('by_guest', 'manager'));
    foreach (['customer1' => 'customer123', 'manager1' => 'manager123', 'rider1' => 'rider123'] as $username => $password) {
        same(403, Client::as($username, $password)->post('/admin/users', $account("by_$username", 'manager'))->status(), $username);
    }
    same(0, (int) scalar("SELECT COUNT(*) FROM users WHERE username LIKE 'by\\_%'"), 'nobody else can create accounts');

    $admin = Client::as('admin', 'admin123');
    foreach (['manager' => '/manager', 'rider' => '/rider'] as $role => $home) {
        $admin->post('/admin/users', $account("new_$role", $role));
        same($role, scalar('SELECT role FROM users WHERE username = ?', ["new_$role"]));
        // The person who receives the details can sign in at once
        same(200, Client::as("new_$role", 'secret-pass-1')->get($home)->status(), $home);
    }
});

test('a pending account waits for administrator approval', function () {
    $admin = Client::as('admin', 'admin123');
    $admin->post('/admin/users', ['username' => 'waiting_rider', 'name' => 'Waiting Rider', 'email' => 'waiting@foodhub.test',
        'phone' => '+8801911111114', 'address' => 'Dhaka', 'role' => 'rider', 'status' => 'pending', 'password' => 'secret-pass-1']);
    $rider = new Client();
    $rider->post('/login', ['login' => 'waiting_rider', 'password' => 'secret-pass-1']);
    check(strpos($rider->flash(), 'waiting for administrator approval') !== false, 'pending cannot sign in');

    $id = (int) scalar("SELECT user_id FROM users WHERE username = 'waiting_rider'");
    $admin->post("/admin/users/$id/approve");
    Client::as('waiting_rider', 'secret-pass-1');
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
