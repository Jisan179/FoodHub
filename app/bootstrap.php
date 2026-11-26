<?php

declare(strict_types=1);

/**
 * Application bootstrap: autoloading, environment and configuration.
 * Returns the configuration array. No third-party dependencies are required.
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/helpers.php';

App\Core\Env::load(BASE_PATH . '/.env');

$config = require BASE_PATH . '/config/config.php';
date_default_timezone_set($config['timezone']);

return $config;
