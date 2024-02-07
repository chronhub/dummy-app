<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\StreamSubscriber;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsStreamSubscriber
{
    public function __construct(
        public string|array $chronicler,
        public ?string $method = null,
        public bool $autoWire = true,
        public int $priority = 0
    ) {
    }
}
