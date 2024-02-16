<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use function in_array;

class InventoryReleaseReason
{
    /**
     * The order associated with the reservation was canceled
     */
    public const ORDER_CANCELLED = 'Order cancelled';

    /**
     * The reservation associated with the order expired
     */
    public const ORDER_EXPIRED = 'Order expired';

    /**
     * The product was returned by the customer
     */
    public const ORDER_RETURNED = 'Order returned';

    /**
     * Manual adjustment or correction of inventory levels
     */
    public const MANUAL_ADJUSTMENT = 'Manual adjustment';

    /**
     * The product was recalled for safety or quality reasons
     */
    public const PRODUCT_RECALLED = 'Product recalled';

    /**
     * The product reached its expiration date
     */
    public const EXPIRED_PRODUCT = 'Expired product';

    /**
     * The product was damaged and cannot be sold
     */
    public const DAMAGED_PRODUCT = 'Damaged product';

    /**
     * Excess inventory that needs to be cleared
     */
    public const EXCESS_INVENTORY = 'Excess inventory';

    /**
     * Correction made during a stockTake or inventory audit
     */
    public const STOCK_TAKE_CORRECTION = 'Stock-take correction';

    /**
     * The product is no longer available for sale
     */
    public const OUT_OF_STOCK = 'Out of stock';

    /**
     * Any other reason not covered by the above
     */
    public const OTHER = 'Other';

    public static function all(): array
    {
        return [
            self::ORDER_CANCELLED,
            self::ORDER_EXPIRED,
            self::ORDER_RETURNED,
            self::MANUAL_ADJUSTMENT,
            self::PRODUCT_RECALLED,
            self::EXPIRED_PRODUCT,
            self::DAMAGED_PRODUCT,
            self::EXCESS_INVENTORY,
            self::STOCK_TAKE_CORRECTION,
            self::OUT_OF_STOCK,
            self::OTHER,
        ];
    }

    public static function isValid(string $reason): bool
    {
        return in_array($reason, self::all(), true);
    }
}
