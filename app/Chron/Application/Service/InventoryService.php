<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Application\Messaging\Command\Inventory\RefillInventoryItem;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\InventoryProvider;
use RuntimeException;

final readonly class InventoryService
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function addNewProductToInventory(string $skuId): void
    {
        Report::relay(
            AddInventoryItem::withItem(
                $skuId,
                fake()->numberBetween(1000, 10000),
                (string) fake()->randomFloat(2, 10, 2000),
            )
        );
    }

    public function increaseInventoryItemQuantity(): void
    {
        $item = $this->inventoryProvider->findRandomItem();

        if ($item === null) {
            throw new RuntimeException('No inventory items found');
        }

        Report::relay(
            RefillInventoryItem::withItem($item->id, fake()->numberBetween(1000, 10000))
        );
    }
}
