<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use Storm\Contract\Reporter\Reporter;

interface Manager
{
    public function get(string $name): Reporter;
}
