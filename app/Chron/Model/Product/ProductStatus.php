<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

enum ProductStatus: string
{
    case AVAILABLE = 'available';
    case DISCONTINUED = 'discontinued';
    case OUT_OF_STOCK = 'out_of_stock';

    public static function toStrings(): array
    {
        return [
            self::AVAILABLE->value,
            self::DISCONTINUED->value,
            self::OUT_OF_STOCK->value,
        ];
    }
}
