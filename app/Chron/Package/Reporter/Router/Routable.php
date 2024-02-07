<?php

declare(strict_types=1);

namespace App\Chron\Package\Reporter\Router;

interface Routable
{
    /**
     * @return array<callable>|null
     */
    public function route(string $reporterId, string $message): ?array;
}
