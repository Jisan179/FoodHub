<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_DEBUG', 'false') === 'true',
    // Used to build links in emails. Never derived from the request's Host header.
    'url' => rtrim(Env::get('APP_URL', 'http://localhost/FoodHub'), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Dhaka'),

    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => (int) Env::get('DB_PORT', '3306'),
        'name' => Env::get('DB_NAME', 'foodhub_v3'),
        'user' => Env::get('DB_USER', 'root'),
        'pass' => Env::get('DB_PASS', ''),
    ],

    'mail' => [
        // "log" writes messages to storage/logs/mail.log; "mail" uses PHP's mail()
        'driver' => Env::get('MAIL_DRIVER', 'log'),
        'from' => Env::get('MAIL_FROM', 'no-reply@foodhub.test'),
        'log_file' => BASE_PATH . '/storage/logs/mail.log',
    ],

    'delivery_fee' => 50.00,
    'rider_commission_percent' => 10,
    'max_item_quantity' => 50,
    'per_page' => 10,

    'uploads' => [
        'dir' => BASE_PATH . '/public/uploads',
        'max_bytes' => 2 * 1024 * 1024,
    ],
];
