<?php

declare(strict_types=1);

namespace App\Chron\Model\Product\Exception;

use App\Chron\Model\DomainException;
use App\Chron\Model\Product\ProductId;

use function sprintf;

class ProductNotFound extends DomainException
{
    public static function withId(ProductId $productId): self
    {
        return new self(sprintf('Product with id %s not found', $productId->toString()));
    }
}
