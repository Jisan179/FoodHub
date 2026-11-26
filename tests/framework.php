<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

/** A very small test framework, so the project needs no dependencies. */
final class Tests
{
    public static array $cases = [];
    public static int $assertions = 0;
    public static array $config;
    public static Router $router;
    public static Database $db;

    /** Empties every table and reloads the demo data. */
    public static function resetDatabase(): void
    {
        $pdo = self::$db->pdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['rate_limits', 'password_resets', 'reviews', 'cart_items', 'favorites', 'deliveries', 'order_status_history', 'order_items', 'orders', 'food_items', 'restaurants', 'users'] as $table) {
            $pdo->exec("TRUNCATE TABLE $table");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $seed = require BASE_PATH . '/database/seed.php';
        self::$db->transaction($seed);
        @unlink(self::$config['mail']['log_file']);
    }
}

function test(string $name, callable $body): void
{
    Tests::$cases[$name] = $body;
}

function check(bool $condition, string $message): void
{
    Tests::$assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function same(mixed $expected, mixed $actual, string $message = ''): void
{
    check($expected === $actual, trim($message . ' expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)));
}

function throws(string $class, callable $body): void
{
    try {
        $body();
    } catch (Throwable $error) {
        check($error instanceof $class, "Expected $class, got " . get_class($error) . ': ' . $error->getMessage());

        return;
    }
    check(false, "Expected $class to be thrown");
}

function row(string $sql, array $params = []): ?array
{
    return Tests::$db->one($sql, $params);
}

function scalar(string $sql, array $params = [])
{
    return Tests::$db->value($sql, $params);
}

/** A browser-like client: one session, requests handled in memory by the real application. */
final class Client
{
    private array $store = [];
    private App $app;

    public function __construct()
    {
        $this->app = new App(Tests::$config, new Session($this->store), '/FoodHub');
    }

    public static function as(string $username, string $password): self
    {
        $client = new self();
        $response = $client->post('/login', ['login' => $username, 'password' => $password]);
        check($response->status() === 302 && strpos($response->header('Location'), '/login') === false, "Could not sign in as $username");

        return $client;
    }

    public function request(string $method, string $path, array $query = [], array $post = [], array $server = []): Response
    {
        $server += ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'localhost'];

        return $this->app->handle(new Request($method, $path, $query, $post, [], $server), Tests::$router);
    }

    public function get(string $path, array $query = []): Response
    {
        return $this->request('GET', $path, $query);
    }

    public function post(string $path, array $data = [], array $server = []): Response
    {
        $session = new Session($this->store);

        return $this->request('POST', $path, [], $data + ['_token' => $session->csrfToken()], $server);
    }

    /** Messages waiting to be shown, such as errors from the last action. */
    public function flash(): string
    {
        return implode(' | ', array_column($this->store['_flash'] ?? [], 'message'));
    }

    public function clearFlash(): void
    {
        unset($this->store['_flash'], $this->store['_errors'], $this->store['_old']);
    }
}
