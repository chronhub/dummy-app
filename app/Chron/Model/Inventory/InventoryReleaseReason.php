<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use function in_array;

class InventoryReleaseReason
{
    public const string RESERVATION_CONFIRMED = 'reservation confirmed';

    public const string RESERVATION_CANCELED = 'reservation canceled';

    public const string RESERVATION_ADJUSTED = 'reservation adjusted';

    /**
     * The reservation expired before the customer could purchase the product
     */
    public const string RESERVATION_EXPIRED = 'reservation expired';

    /**
     * The product was returned by the customer
     */
    public const string RESERVATION_RETURNED = 'reservation returned';

    /**
     * Manual adjustment or correction of inventory levels
     */
    public const string MANUAL_ADJUSTMENT = 'Manual adjustment';

    /**
     * The product was recalled for safety or quality reasons
     */
    public const string PRODUCT_RECALLED = 'product recalled';

    /**
     * The product reached its expiration date
     */
    public const string EXPIRED_PRODUCT = 'Expired product';

    /**
     * The product was damaged and cannot be sold
     */
    public const string DAMAGED_PRODUCT = 'damaged product';

    /**
     * Excess inventory that needs to be cleared
     */
    public const string EXCESS_INVENTORY = 'Excess inventory';

    /**
     * Correction made during a stockTake or inventory audit
     */
    public const string STOCK_TAKE_CORRECTION = 'Stock-take correction';

    /**
     * Any other reason not covered by the above
     */
    public const string OTHER = 'Other';

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
