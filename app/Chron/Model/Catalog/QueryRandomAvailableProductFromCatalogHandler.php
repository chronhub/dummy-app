<?php

declare(strict_types=1);

namespace App\Chron\Model\Catalog;

use App\Chron\Application\Messaging\Command\Catalog\QueryRandomAvailableProductFromCatalog;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use App\Chron\Projection\ReadModel\CatalogReadModel;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomAvailableProductFromCatalog::class,
)]
final readonly class QueryRandomAvailableProductFromCatalogHandler
{
    public function __construct(private CatalogReadModel $catalogReadModel)
    {
    }

    public function __invoke(QueryRandomAvailableProductFromCatalog $query, Deferred $promise): void
    {
        $product = $this->catalogReadModel->findRandomAvailableProduct();

        $promise->resolve($product);
    }
}
