<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Query;

final class QueryPaginatedCustomers
{
    public function __construct(public int $page, public int $perPage)
    {
    }
}
