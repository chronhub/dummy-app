<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Service;

use App\Chron\Model\Inventory\Exception\InventoryItemNotFound;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\InventoryReleaseReason;
use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Inventory\SkuId;

// todo context mapping
final readonly class InventoryReservationService
{
    public function __construct(private InventoryList $inventoryList)
    {
    }

    public function reserveItem(string $skuId, int $requested): false|Quantity
    {
        $inventory = $this->getInventory($skuId);

        $availableQuantity = $inventory->getAvailableQuantity(
            $this->reservationQuantity($requested)
        );

        if ($availableQuantity === false) {
            return false;
        }

        $inventory->reserve($availableQuantity);

        $this->inventoryList->save($inventory);

        return $availableQuantity;
    }

    public function releaseItem(string $skuId, int $requested, string $reason = InventoryReleaseReason::OTHER): void
    {
        $inventory = $this->getInventory($skuId);

        $inventory->release($this->reservationQuantity($requested), $reason);

        $this->inventoryList->save($inventory);
    }

    private function getInventory(string $skuId): Inventory
    {
        $skuId = SkuId::fromString($skuId);

        $inventory = $this->inventoryList->get($skuId);

        if (! $inventory instanceof Inventory) {
            throw InventoryItemNotFound::withId($skuId);
        }

        return $inventory;
    }

    private function reservationQuantity(int $requested): Quantity
    {
        return Quantity::create($requested);
    }
}
