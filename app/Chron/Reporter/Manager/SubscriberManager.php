<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;

interface SubscriberManager
{
    public function get(string $reporterId, DomainType $type): array;
}
