<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Handler;

use App\Chron\Application\Messaging\Command\Inventory\QueryRandomProductInventory;
use App\Chron\Projection\Provider\InventoryProvider;
use React\Promise\Deferred;
use Storm\Message\Attribute\AsQueryHandler;

#[AsQueryHandler(
    reporter: 'reporter.query.default',
    handles: QueryRandomProductInventory::class,
)]
final readonly class QueryRandomProductInventoryHandler
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function __invoke(QueryRandomProductInventory $query, Deferred $promise): void
    {
        $item = $this->inventoryProvider->findRandomItem();

        $promise->resolve($item);
    }
}
