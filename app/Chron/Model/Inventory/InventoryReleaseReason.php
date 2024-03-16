<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use function in_array;

class InventoryReleaseReason
{
    public const RESERVATION_CONFIRMED = 'reservation confirmed';

    public const RESERVATION_CANCELED = 'reservation canceled';

    public const RESERVATION_ADJUSTED = 'reservation adjusted';

    /**
     * The reservation expired before the customer could purchase the product
     */
    public const RESERVATION_EXPIRED = 'reservation expired';

    /**
     * The product was returned by the customer
     */
    public const RESERVATION_RETURNED = 'reservation returned';

    /**
     * Manual adjustment or correction of inventory levels
     */
    public const MANUAL_ADJUSTMENT = 'Manual adjustment';

    /**
     * The product was recalled for safety or quality reasons
     */
    public const PRODUCT_RECALLED = 'product recalled';

    /**
     * The product reached its expiration date
     */
    public const EXPIRED_PRODUCT = 'Expired product';

    /**
     * The product was damaged and cannot be sold
     */
    public const DAMAGED_PRODUCT = 'damaged product';

    /**
     * Excess inventory that needs to be cleared
     */
    public const EXCESS_INVENTORY = 'Excess inventory';

    /**
     * Correction made during a stockTake or inventory audit
     */
    public const STOCK_TAKE_CORRECTION = 'Stock-take correction';

    /**
     * Any other reason not covered by the above
     */
    public const OTHER = 'Other';

    public static function all(): array
    {
        return [
            self::RESERVATION_CANCELED,
            self::RESERVATION_ADJUSTED,
            self::RESERVATION_EXPIRED,
            self::RESERVATION_RETURNED,
            self::MANUAL_ADJUSTMENT,
            self::PRODUCT_RECALLED,
            self::EXPIRED_PRODUCT,
            self::DAMAGED_PRODUCT,
            self::EXCESS_INVENTORY,
            self::STOCK_TAKE_CORRECTION,
            self::OTHER,
        ];
    }

    public static function isValid(string $reason): bool
    {
        return in_array($reason, self::all(), true);
    }
}
