<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DomainError;

/** The cart already holds items from another restaurant. */
final class CartConflict extends DomainError
{
}
