<?php

declare(strict_types=1);

// PHP's built-in server: let it serve real files such as CSS and images
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

$config = require dirname(__DIR__) . '/app/bootstrap.php';

ini_set('display_errors', $config['debug'] ? '1' : '0');
ini_set('log_errors', '1');

// Work out the URL prefix, whether the web root is public/ or the project folder
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (substr($basePath, -7) === '/public' && strpos($requestPath, $basePath) !== 0) {
    $basePath = substr($basePath, 0, -7);
}

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$session = App\Core\Session::startNative($https);
$router = require BASE_PATH . '/app/routes.php';

(new App\Core\App($config, $session, $basePath))
    ->handle(App\Core\Request::fromGlobals($basePath), $router)
    ->send();
