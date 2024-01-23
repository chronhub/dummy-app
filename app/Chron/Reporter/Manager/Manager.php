<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use Storm\Contract\Reporter\Reporter;

interface Manager
{
    /**
     * Name one of 'reporter' or 'handler' string
     */
    public function get(string $name): Reporter;
}
