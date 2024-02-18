<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Handler;

use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Model\Product\Exception\ProductAlreadyExists;
use App\Chron\Model\Product\Product;
use App\Chron\Model\Product\Repository\ProductList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CreateProduct::class,
)]
final readonly class CreateProductHandler
{
    public function __construct(private ProductList $products)
    {
    }

    public function __invoke(CreateProduct $command): void
    {
        $productId = $command->productId();

        if ($this->products->get($productId) !== null) {
            throw ProductAlreadyExists::withId($productId);
        }

        $product = Product::create($productId, $command->productInfo());

        $this->products->save($product);
    }
}
