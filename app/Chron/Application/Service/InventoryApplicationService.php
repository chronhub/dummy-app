<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Factory\ProductFactory;
use App\Chron\Application\Messaging\Command\Inventory\AddInventoryItem;
use App\Chron\Application\Messaging\Command\Inventory\QueryRandomProductInventory;
use App\Chron\Application\Messaging\Command\Inventory\RefillInventoryItem;
use App\Chron\Package\Reporter\Report;
use App\Chron\Package\Support\QueryPromiseTrait;
use DomainException;
use stdClass;

final readonly class InventoryApplicationService
{
    use QueryPromiseTrait;

    public function feedInventory(string $skuId): void
    {
        $data = ProductFactory::makeProductItem($skuId);

        $command = AddInventoryItem::withItem(...$data);

        Report::relay($command);
    }

    public function refillProductInventory(): void
    {
        $product = $this->queryRandomProductInventory();

        $command = RefillInventoryItem::withItem(
            $product->id,
            ProductFactory::createProductQuantity()
        );

        Report::relay($command);
    }

    private function queryRandomProductInventory(): stdClass
    {
        $query = new QueryRandomProductInventory();

        $product = $this->handlePromise(Report::relay($query));

        if (! $product instanceof stdClass) {
            throw new DomainException('No inventory items found');
        }

        return $product;
    }
}
