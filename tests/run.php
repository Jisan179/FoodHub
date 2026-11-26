<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Tests run from the command line only.');
}

$config = require dirname(__DIR__) . '/app/bootstrap.php';
require __DIR__ . '/framework.php';

// Tests always use a separate, disposable database
$config['db']['name'] = getenv('TEST_DB_NAME') ?: 'foodhub_test';
if (substr($config['db']['name'], -5) !== '_test') {
    fwrite(STDERR, "Refusing to run: the test database name must end with _test.\n");
    exit(1);
}
$config['debug'] = true;
$config['mail'] = ['driver' => 'log', 'from' => 'test@foodhub.test', 'log_file' => sys_get_temp_dir() . '/foodhub-test-mail.log'];

try {
    $migrator = new App\Core\Migrator($config['db']);
    $migrator->drop();
    $migrator->migrate();
} catch (Throwable $error) {
    fwrite(STDERR, 'Could not prepare the test database: ' . $error->getMessage() . "\n");
    exit(1);
}

Tests::$config = $config;
Tests::$db = new App\Core\Database($config['db']);
Tests::$router = require BASE_PATH . '/app/routes.php';

foreach (glob(__DIR__ . '/*Test.php') as $file) {
    require $file;
}

$only = $argv[1] ?? '';
$failed = 0;
$ran = 0;
foreach (Tests::$cases as $name => $body) {
    if ($only !== '' && stripos($name, $only) === false) {
        continue;
    }
    $ran++;
    try {
        Tests::resetDatabase();
        $body();
        echo "  PASS  $name\n";
    } catch (Throwable $error) {
        $failed++;
        echo "  FAIL  $name\n        " . $error->getMessage() . "\n        at " . basename($error->getFile()) . ':' . $error->getLine() . "\n";
    }
}
$migrator->drop();

echo "\n$ran tests, " . Tests::$assertions . " assertions, $failed failed\n";
exit($failed === 0 && $ran > 0 ? 0 : 1);
