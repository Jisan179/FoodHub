<?php

declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    public int $page;
    public int $perPage;
    public int $total;
    public int $pages;

    public function __construct(int $total, int $page, int $perPage)
    {
        $this->total = max(0, $total);
        $this->perPage = max(1, $perPage);
        $this->pages = max(1, (int) ceil($this->total / $this->perPage));
        $this->page = min(max(1, $page), $this->pages);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
