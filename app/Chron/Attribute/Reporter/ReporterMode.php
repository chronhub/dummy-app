<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

readonly class ReporterMode
{
    public function __construct(
        public string $id,
        public Enqueue $enqueue,
        public null|string|array $default
    ) {
    }
}
