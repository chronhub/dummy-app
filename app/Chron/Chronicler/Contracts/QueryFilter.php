<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Contracts;

interface QueryFilter
{
    public function apply(): callable;
}
