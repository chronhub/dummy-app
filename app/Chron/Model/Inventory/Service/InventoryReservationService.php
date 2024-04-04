<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory\Service;

use App\Chron\Model\DomainException;
use App\Chron\Model\Inventory\Exception\InsufficientQuantityForReservation;
use App\Chron\Model\Inventory\Exception\InventoryItemNotFound;
use App\Chron\Model\Inventory\Exception\InventoryOutOfStock;
use App\Chron\Model\Inventory\Inventory;
use App\Chron\Model\Inventory\InventoryReleaseReason;
use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Inventory\SkuId;
use Storm\Chronicler\Exceptions\ConcurrencyException;

final readonly class InventoryReservationService
{
    public function __construct(private InventoryList $inventoryList)
    {
    }

    /**
     * @throws InventoryOutOfStock
     * @throws InsufficientQuantityForReservation
     * @throws DomainException
     * @throws ConcurrencyException
     */
    public function reserveItem(string $skuId, int $requested): void
    {
        $inventory = $this->getInventory($skuId);

        $inventory->reserve(PositiveQuantity::create($requested));

        $this->inventoryList->save($inventory);
    }

    // todo handle case when quantity to release is greater than reserved quantity
    public function releaseItem(string $skuId, int $requested, string $reason = InventoryReleaseReason::OTHER): void
    {
        $inventory = $this->getInventory($skuId);

        $inventory->release(PositiveQuantity::create($requested), $reason);

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
}
