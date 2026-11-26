<?php

declare(strict_types=1);

namespace App\Core;

/** Plain PHP templates. Inside a template, $this refers to this class. */
final class View
{
    private string $basePath;
    private Session $session;
    private array $shared = [];
    private ?string $capturedTitle = null;

    public function __construct(string $basePath, Session $session)
    {
        $this->basePath = $basePath;
        $this->session = $session;
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function render(string $template, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $data += $this->shared;
        $data['errors'] = $data['errors'] ?? $this->session->get('_errors', []);
        $content = $this->capture($template, $data);
        if ($layout === null) {
            return $content;
        }
        // A page template may set its own $title for the layout
        $data['title'] = $this->capturedTitle ?? $data['title'] ?? null;
        $data['content'] = $content;
        $data['flashes'] = $this->session->pull('_flash', []);

        return $this->capture($layout, $data);
    }

    private function capture(string $__template, array $__data): string
    {
        extract($__data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/resources/views/' . $__template . '.php';
        $this->capturedTitle = $title ?? null;

        return (string) ob_get_clean();
    }

    public function partial(string $template, array $data = []): string
    {
        return $this->capture($template, $data + $this->shared + ['errors' => $this->session->get('_errors', [])]);
    }

    /** Application URL, safe to print inside an attribute. */
    public function url(string $path = '/', array $query = []): string
    {
        $url = $this->basePath . '/' . ltrim($path, '/');
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return e($url);
    }

    public function asset(string $path): string
    {
        return $this->url($path);
    }

    public function csrf(): string
    {
        return '<input type="hidden" name="_token" value="' . e($this->session->csrfToken()) . '">';
    }

    /** Value the user submitted last time, after a validation error. */
    public function old(string $field, $default = ''): string
    {
        $old = $this->session->get('_old', []);

        return e($old[$field] ?? $default);
    }

    public function rawOld(string $field, $default = '')
    {
        return $this->session->get('_old', [])[$field] ?? $default;
    }

    public function error(string $field): string
    {
        $errors = $this->session->get('_errors', []);

        return isset($errors[$field]) ? '<small class="field-error">' . e($errors[$field]) . '</small>' : '';
    }
}
