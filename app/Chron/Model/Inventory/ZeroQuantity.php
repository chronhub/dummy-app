<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

final readonly class ZeroQuantity
{
    public int $value;

    private function __construct()
    {
        $this->value = 0;
    }

    public static function create(): self
    {
        return new self();
    }
}
