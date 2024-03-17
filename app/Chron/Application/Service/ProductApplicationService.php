<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Factory\ProductFactory;
use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Package\Reporter\Report;
use Symfony\Component\Uid\Uuid;

final class ProductApplicationService
{
    public function createProducts(): void
    {
        $products = ProductFactory::makeProducts();

        foreach ($products as $product) {
            $command = CreateProduct::withProduct(Uuid::v4()->jsonSerialize(), $product);

            Report::relay($command);
        }
    }
}
