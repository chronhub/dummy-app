<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;

interface SusbcriberManager
{
    public function get(string $reporterId, DomainType $type): array;
}
