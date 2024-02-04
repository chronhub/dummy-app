<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Exception;

use RuntimeException;

class ConnectionConcurrencyFailure extends RuntimeException
{
}
