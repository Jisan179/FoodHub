<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** A broken business rule. The message is safe to show to the user. */
class DomainError extends RuntimeException
{
}
