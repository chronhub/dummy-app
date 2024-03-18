<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

final class OrderCanceledReason
{
    public const string CUSTOMER_REQUESTED = 'customer_requested';

    public const string ORDER_EXPIRED = 'order_expired';

    public const string OTHER = 'other';

    public const ALL = [
        self::CUSTOMER_REQUESTED,
        self::ORDER_EXPIRED,
        self::OTHER,
    ];
}
