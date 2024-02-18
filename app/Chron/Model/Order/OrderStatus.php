<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

enum OrderStatus: string
{
    case CREATED = 'created';

    case MODIFIED = 'modified';

    case CANCELED = 'canceled';

    case PAID = 'paid';

    case SHIPPED = 'shipped';

    case DELIVERED = 'delivered';

    case RETURNED = 'returned';

    case REFUNDED = 'refunded';

    case CLOSED = 'closed';

    public static function toStrings(): array
    {
        return [
            self::CREATED->value,
            self::MODIFIED->value,
            self::CANCELED->value,
            self::PAID->value,
            self::SHIPPED->value,
            self::DELIVERED->value,
            self::RETURNED->value,
            self::REFUNDED->value,
            self::CLOSED->value,
        ];
    }
}
