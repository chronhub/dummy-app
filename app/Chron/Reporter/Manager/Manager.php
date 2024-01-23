<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;
use Storm\Contract\Reporter\Reporter;

interface Manager
{
    public function create(string $name, string|DomainType $type): Reporter;

    public function command(?string $name = null): Reporter;

    public function event(?string $name = null): Reporter;

    public function query(?string $name = null): Reporter;

    public function getDefaultId(string $type): string;
}
