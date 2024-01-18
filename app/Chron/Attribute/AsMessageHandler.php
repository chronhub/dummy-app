<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsMessageHandler
{
    public function __construct(
        public string $reporter,
        public ?string $fromTransport = null,
        public ?string $handles = null,
        public ?string $method = null,
        public int $priority = 0,
    ) {
        if (blank($this->reporter)) {
            throw new InvalidArgumentException('Reporter name cannot be blank');
        }
    }
}
