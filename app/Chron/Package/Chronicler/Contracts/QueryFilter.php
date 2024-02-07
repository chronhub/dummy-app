<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Contracts;

interface QueryFilter
{
    public function apply(): callable;
}
