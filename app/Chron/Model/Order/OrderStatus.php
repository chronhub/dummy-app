<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

enum OrderStatus: string
{
    case CREATED = 'created';

    case MODIFIED = 'modified';

    case COMPLETED = 'completed';

    case CANCELLED = 'cancelled';

    case PAID = 'paid';

    case SHIPPED = 'shipped';

    case DELIVERED = 'delivered';

    case RETURNED = 'returned';

    case REFUNDED = 'refunded';
}
