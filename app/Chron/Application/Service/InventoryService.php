<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Application\Messaging\Command\Inventory\IncreaseInventoryItemQuantity;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\InventoryProvider;

final readonly class InventoryService
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function addNewProductToInventory(string $skuId, string $itemId): void
    {
        Report::relay(
            AddInventoryItem::withItem(
                $skuId,
                $itemId,
                fake()->numberBetween(1000, 10000),
                (string) fake()->randomFloat(2, 10, 4000),
            )
        );
    }

    public function increaseInventoryItemQuantity(): void
    {
        $item = $this->inventoryProvider->findRandomInventoryItem();
        if ($item === null) {
            return;
        }

        // todo alert when quantity decreased
        Report::relay(
            IncreaseInventoryItemQuantity::withItem(
                $item->id,
                $item->item_id,
                fake()->numberBetween(1000, 10000),
            )
        );
    }
}
