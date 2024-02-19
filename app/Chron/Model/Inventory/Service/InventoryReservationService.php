<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Service;

use App\Chron\Model\Inventory\Exception\InventoryItemNotFound;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\InventoryReleaseReason;
use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Quantity;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Inventory\SkuId;
use LogicException;

// todo context mapping
final readonly class InventoryReservationService
{
    public function __construct(private InventoryList $inventoryList)
    {
    }

    public function reserveItem(string $skuId, int $requested): false|PositiveQuantity
    {
        $inventory = $this->getInventory($skuId);

        if ($inventory->isOutOfStock()) {
            return false;
        }

        $quantityRequested = $this->reservationQuantity($requested);

        $availableQuantity = $inventory->getAvailableQuantity($quantityRequested);

        if ($availableQuantity === false || $availableQuantity->value === 0) {
            throw new LogicException('Available quantity should not be false or zero.');
        }

        $inventory->reserve($quantityRequested);

        $this->inventoryList->save($inventory);

        if ($quantityRequested->value > $availableQuantity->value) {
            return $availableQuantity;
        }

        return $quantityRequested;
    }

    // todo handle case when quantity to release is greater than reserved quantity
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

    private function reservationQuantity(int $requested): PositiveQuantity
    {
        return PositiveQuantity::create($requested);
    }
}
