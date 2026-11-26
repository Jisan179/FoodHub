<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/** The HTTP kernel: routing, CSRF, access control, error handling and shared services. */
final class App
{
    public array $config;
    public Session $session;
    public View $view;
    public string $basePath;
    private ?Database $db = null;
    private array $instances = [];
    private ?array $user = null;
    private bool $userLoaded = false;

    public function __construct(array $config, Session $session, string $basePath = '')
    {
        $this->config = $config;
        $this->session = $session;
        $this->basePath = rtrim($basePath, '/');
        $this->view = new View($this->basePath, $session);
    }

    public function db(): Database
    {
        return $this->db ??= new Database($this->config['db']);
    }

    /** Shared instance of a model or service whose constructor takes this App. */
    public function make(string $class)
    {
        return $this->instances[$class] ??= new $class($this);
    }

    /** The signed-in user, re-read on every request so status changes apply at once. */
    public function user(): ?array
    {
        if (!$this->userLoaded) {
            $this->userLoaded = true;
            $id = (int) $this->session->get('user_id', 0);
            if ($id > 0) {
                $user = $this->db()->one('SELECT * FROM users WHERE user_id = ?', [$id]);
                // A password change signs out every other session
                if ($user && $user['status'] === 'active' && hash_equals((string) $this->session->get('auth_stamp'), self::authStamp($user))) {
                    unset($user['password_hash']);
                    $this->user = $user;
                } else {
                    $this->session->invalidate();
                    $this->session->flash('error', 'Your session has ended. Please sign in again.');
                }
            }
        }

        return $this->user;
    }

    public function login(array $user): void
    {
        $this->session->invalidate();
        $this->session->put('user_id', (int) $user['user_id']);
        $this->session->put('auth_stamp', self::authStamp($user));
        $this->userLoaded = false;
        $this->user = null;
    }

    public function logout(): void
    {
        $this->session->invalidate();
        $this->userLoaded = true;
        $this->user = null;
    }

    public static function authStamp(array $user): string
    {
        return hash('sha256', $user['password_hash']);
    }

    public function url(string $path = '/'): string
    {
        return $this->basePath . '/' . ltrim($path, '/');
    }

    public static function homeFor(string $role): string
    {
        return ['admin' => '/admin', 'manager' => '/manager', 'rider' => '/rider'][$role] ?? '/customer';
    }

    public function handle(Request $request, Router $router): Response
    {
        $this->userLoaded = false;
        $this->user = null;
        try {
            $response = $this->dispatch($request, $router);
        } catch (ValidationError $error) {
            $old = $request->post();
            unset($old['password'], $old['password_confirmation'], $old['current_password'], $old['_token']);
            $this->session->put('_old', array_filter($old, 'is_string'));
            $this->session->put('_errors', $error->errors());
            $this->session->flash('error', implode(' ', $error->errors()));
            $response = $this->back($request);
        } catch (DomainError $error) {
            $this->session->flash('error', $error->getMessage());
            $response = $this->back($request);
        } catch (HttpException $error) {
            $response = $this->errorPage($error->getCode(), $error->getMessage());
        } catch (Throwable $error) {
            error_log((string) $error);
            $response = $this->errorPage(500, $this->config['debug'] ? $error->getMessage() : '');
        }

        return $response->withHeaders([
            'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'",
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'same-origin',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function dispatch(Request $request, Router $router): Response
    {
        $route = $router->match($request->method(), $request->path());

        if ($request->method() === 'POST') {
            $sent = $request->string('_token') ?: $request->header('X-CSRF-Token');
            if (!hash_equals($this->session->csrfToken(), $sent)) {
                throw new HttpException(419, 'Your form expired. Go back, refresh the page and try again.');
            }
        }

        $user = $this->user();
        $access = $route['access'];
        if ($access === 'guest' && $user) {
            return Response::redirect($this->url(self::homeFor($user['role'])));
        }
        if ($access !== 'guest' && $access !== 'public') {
            if (!$user) {
                return Response::redirect($this->url('/login'));
            }
            if ($access !== 'auth' && $user['role'] !== $access) {
                throw new HttpException(403, 'You do not have permission to open this page.');
            }
        }
        $this->shareWithViews($user, $request);

        [$class, $method] = $route['action'];
        $response = (new $class($this, $request))->$method(...array_values($route['params']));

        // Old input and errors are shown once
        if ($request->method() === 'GET') {
            $this->session->forget('_old');
            $this->session->forget('_errors');
        }

        return $response;
    }

    private function shareWithViews(?array $user, Request $request): void
    {
        $this->view->share('currentUser', $user);
        $this->view->share('currentPath', $request->path());
        $cartCount = 0;
        if ($user && $user['role'] === 'customer') {
            $cartCount = (int) $this->db()->value('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE customer_id = ?', [(int) $user['user_id']]);
        }
        $this->view->share('cartCount', $cartCount);
    }

    /** Redirect to the referring page, but only when it belongs to this application. */
    public function back(Request $request, string $fallback = '/'): Response
    {
        $parts = parse_url($request->header('Referer'));
        $path = $parts['path'] ?? '';
        $sameHost = ($parts['host'] ?? '') !== ''
            && strcasecmp($parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : ''), $request->header('Host')) === 0;
        if ($sameHost && strpos($path, $this->basePath . '/') === 0 && strpos($path, '//') !== 0) {
            return Response::redirect($path . (isset($parts['query']) ? '?' . $parts['query'] : ''));
        }

        return Response::redirect($this->url($fallback));
    }

    private function errorPage(int $status, string $message): Response
    {
        $titles = [403 => 'Access denied', 404 => 'Page not found', 405 => 'Method not allowed', 419 => 'Page expired', 500 => 'Something went wrong'];
        $this->view->share('currentUser', null);
        $this->view->share('currentPath', '');
        $this->view->share('cartCount', 0);
        $body = $this->view->render('errors/error', [
            'status' => $status,
            'title' => $titles[$status] ?? 'Error',
            'message' => $message,
        ]);

        return new Response($body, $status);
    }
}
