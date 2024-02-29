<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Query\QueryFirstTenInventoryItems;
use App\Chron\Package\Attribute\Messaging\AsQueryHandler;
use App\Chron\Projection\Provider\InventoryProvider;
use React\Promise\Deferred;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryFirstTenInventoryItems::class,
)]
final readonly class QueryFirstTenInventoryItemsHandler
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function __invoke(QueryFirstTenInventoryItems $query, Deferred $promise): void
    {
        $inventory = $this->inventoryProvider->getFirstTenItems();

        $promise->resolve($inventory);
    }
}
