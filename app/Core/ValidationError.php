<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class ValidationError extends RuntimeException
{
    /** @var array<string, string> field name => message */
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation failed');
        $this->errors = $errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
