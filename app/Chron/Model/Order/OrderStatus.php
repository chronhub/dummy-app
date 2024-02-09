<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use function in_array;

enum OrderStatus: string
{
    case CREATED = 'created';

    case MODIFIED = 'modified';

    case COMPLETED = 'completed'; // remove

    case CANCELLED = 'cancelled';

    case PAID = 'paid';

    case SHIPPED = 'shipped';

    case DELIVERED = 'delivered';

    case RETURNED = 'returned';

    case REFUNDED = 'refunded';

    public function isPending(): bool
    {
        return in_array($this, [self::CREATED, self::MODIFIED]);
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::REFUNDED]);
    }

    public static function toStrings(): array
    {
        return [
            self::CREATED,
            self::MODIFIED,
            self::COMPLETED,
            self::CANCELLED,
            self::PAID,
            self::SHIPPED,
            self::DELIVERED,
            self::RETURNED,
            self::REFUNDED,
        ];
    }
}
