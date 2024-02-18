<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

final class OrderCanceledReason
{
    public const CUSTOMER_REQUESTED = 'customer_requested';

    public const ORDER_EXPIRED = 'order_expired';

    public const OTHER = 'other';

    public const REASON = [
        self::CUSTOMER_REQUESTED,
        self::ORDER_EXPIRED,
        self::OTHER,
    ];
}
