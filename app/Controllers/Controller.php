<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\ImageUploader;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

abstract class Controller
{
    protected App $app;
    protected Request $request;

    public function __construct(App $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    protected function make(string $class)
    {
        return $this->app->make($class);
    }

    protected function user(): array
    {
        return $this->app->user();
    }

    protected function userId(): int
    {
        return (int) $this->app->user()['user_id'];
    }

    protected function view(string $template, array $data = []): Response
    {
        return new Response($this->app->view->render($template, $data));
    }

    protected function redirect(string $path, ?string $success = null): Response
    {
        if ($success !== null) {
            $this->app->session->flash('success', $success);
        }

        return Response::redirect($this->app->url($path));
    }

    protected function back(string $fallback, ?string $success = null): Response
    {
        if ($success !== null) {
            $this->app->session->flash('success', $success);
        }

        return $this->app->back($this->request, $fallback);
    }

    /** Validates submitted fields. Passwords are kept exactly as typed. */
    protected function validate(array $rules, array $labels = []): array
    {
        $data = [];
        foreach (array_keys($rules) as $field) {
            $data[$field] = strpos($field, 'password') !== false ? $this->request->secret($field) : $this->request->string($field);
        }

        return Validator::check($data, $rules, $labels);
    }

    protected function paginator(int $total): Paginator
    {
        return new Paginator($total, $this->request->int('page', 1), (int) $this->app->config['per_page']);
    }

    protected function images(): ImageUploader
    {
        return new ImageUploader($this->app->config['uploads']);
    }
}
