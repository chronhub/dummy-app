<?php

declare(strict_types=1);

namespace App\Chron\Application\Command\Order;

use Storm\Message\AbstractDomainCommand;

final class CompleteOrder extends AbstractDomainCommand
{
    public static function forCustomer(string $orderId, string $customerId): self
    {
        return new self([
            'order_id' => $orderId,
            'customer_id' => $customerId,
        ]);
    }
}
