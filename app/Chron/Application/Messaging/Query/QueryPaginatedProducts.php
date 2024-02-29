<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Query;

final readonly class QueryPaginatedProducts
{
    public function __construct(public int $limit)
    {
    }
}
