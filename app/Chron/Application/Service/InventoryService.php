<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Application\Messaging\Command\Inventory\RefillInventoryItem;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\InventoryProvider;
use Illuminate\Support\Collection;
use stdClass;

final readonly class InventoryService
{
    public function __construct(private InventoryProvider $inventoryProvider)
    {
    }

    public function getRandomItems(int $limit = 10): Collection
    {
        return $this->inventoryProvider->findRandomItems($limit);
    }

    public function getRandomItem(): stdClass
    {
        return $this->inventoryProvider->findRandomItem();
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
        $item = $this->inventoryProvider->findRandomItem();
        if ($item === null) {
            return;
        }

        // todo alert when quantity decreased
        Report::relay(
            RefillInventoryItem::withItem(
                $item->id,
                $item->item_id,
                fake()->numberBetween(1000, 10000),
            )
        );
    }

    public function reserveItem(string $skuId, string $itemId, int $quantity): void
    {
        // todo
    }
}
