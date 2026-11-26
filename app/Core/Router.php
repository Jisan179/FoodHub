<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routes map a method and path pattern to a controller action.
 * Access is declared per route: 'guest', 'auth' or a role name.
 */
final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, array $action, string $access = 'auth'): void
    {
        $regex = preg_replace_callback(
            '/\{(\w+)\}/',
            static fn (array $m) => $m[1] === 'token' ? '(?P<token>[a-f0-9]{64})' : '(?P<' . $m[1] . '>\d{1,10})',
            $pattern
        );
        $this->routes[] = compact('method', 'regex', 'action', 'access', 'pattern');
    }

    public function get(string $pattern, array $action, string $access = 'auth'): void
    {
        $this->add('GET', $pattern, $action, $access);
    }

    public function post(string $pattern, array $action, string $access = 'auth'): void
    {
        $this->add('POST', $pattern, $action, $access);
    }

    public function all(): array
    {
        return $this->routes;
    }

    /** @return array{action: array, access: string, params: array} */
    public function match(string $method, string $path): array
    {
        $pathExists = false;
        foreach ($this->routes as $route) {
            if (!preg_match('#^' . $route['regex'] . '$#', $path, $matches)) {
                continue;
            }
            $pathExists = true;
            if ($route['method'] !== $method) {
                continue;
            }
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $key === 'token' ? $value : (int) $value;
                }
            }

            return ['action' => $route['action'], 'access' => $route['access'], 'params' => $params];
        }

        throw new HttpException($pathExists ? 405 : 404);
    }
}
