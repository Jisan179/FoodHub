<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $post;
    private array $files;
    private array $server;

    public function __construct(string $method, string $path, array $query = [], array $post = [], array $files = [], array $server = [])
    {
        $this->method = strtoupper($method);
        $this->path = '/' . trim($path, '/');
        $this->query = $query;
        $this->post = $post;
        $this->files = $files;
        $this->server = $server;
    }

    public static function fromGlobals(string $basePath): self
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', rawurldecode($uri), $_GET, $_POST, $_FILES, $_SERVER);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** A trimmed string from the query string or form body. Arrays are rejected. */
    public function string(string $key, string $default = ''): string
    {
        $value = $this->post[$key] ?? $this->query[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }

    /** Raw string without trimming, for passwords. */
    public function secret(string $key): string
    {
        $value = $this->post[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->string($key, (string) $default);

        return preg_match('/^-?\d{1,10}$/', $value) ? (int) $value : $default;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function post(): array
    {
        return $this->post;
    }

    /** The uploaded file entry, or null when no file was chosen. */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || !isset($file['error']) || is_array($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function header(string $name): string
    {
        return (string) ($this->server['HTTP_' . strtoupper(str_replace('-', '_', $name))] ?? '');
    }
}
