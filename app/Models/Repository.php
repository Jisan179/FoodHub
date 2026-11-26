<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Database;

abstract class Repository
{
    protected App $app;
    protected Database $db;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->db();
    }

    /** Escape LIKE wildcards in user search terms. */
    protected function like(string $term): string
    {
        return '%' . addcslashes($term, '%_\\') . '%';
    }
}
