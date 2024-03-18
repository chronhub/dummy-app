<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Catalog;

final readonly class QueryProductFromCatalog
{
    public function __construct(public string $productId)
    {
    }
}
