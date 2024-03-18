<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Projection\Provider\CatalogProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class CatalogApplicationService
{
    public function __construct(private CatalogProvider $catalogProvider)
    {
    }

    public function readCatalog(): LengthAwarePaginator
    {
        return $this->catalogProvider->getPaginatedProducts(10);
    }
}
