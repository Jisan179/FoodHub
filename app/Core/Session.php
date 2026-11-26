<?php

declare(strict_types=1);

namespace App\Core;

/** Session wrapper over an array, so tests can run without real PHP sessions. */
final class Session
{
    private array $data;

    public function __construct(array &$store)
    {
        $this->data = &$store;
    }

    public static function startNative(bool $secure): self
    {
        ini_set('session.use_strict_mode', '1');
        session_name('foodhub_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        return new self($_SESSION);
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function put(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($this->data[$key]);
    }

    public function pull(string $key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    public function flash(string $type, string $message): void
    {
        $messages = $this->get('_flash', []);
        $messages[] = ['type' => $type, 'message' => $message];
        $this->put('_flash', $messages);
    }

    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function invalidate(): void
    {
        $this->data = [];
        $this->regenerate();
    }

    public function csrfToken(): string
    {
        if (empty($this->data['_token'])) {
            $this->data['_token'] = bin2hex(random_bytes(32));
        }

        return $this->data['_token'];
    }
}
