<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\Reporter;

readonly class ReporterQueue
{
    public function __construct(
        public string $id,
        public Mode $mode,
        public null|string|array $default
    ) {
    }
}
