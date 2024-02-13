<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Handler;

use App\Chron\Application\Messaging\Command\Product\CreateProduct;
use App\Chron\Model\Product\Exception\ProductAlreadyExists;
use App\Chron\Model\Product\Product;
use App\Chron\Model\Product\ProductId;
use App\Chron\Model\Product\ProductInfo;
use App\Chron\Model\Product\Repository\ProductList;
use App\Chron\Model\Product\SkuId;
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
        $productId = ProductId::fromString($command->content['product_id']);

        if ($this->products->get($productId) !== null) {
            throw ProductAlreadyExists::withId($productId);
        }

        //wip
        $skuId = SkuId::create();

        $product = Product::create($productId, $skuId, ProductInfo::fromArray($command->content['product_info']));

        $this->products->save($product);
    }
}
