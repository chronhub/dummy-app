<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Command;

use Storm\Message\AbstractDomainCommand;

final class CreateOrder extends AbstractDomainCommand
{
    public static function forCustomer(string $customerId, string $orderId): self
    {
        return new self([
            'customer_id' => $customerId,
            'order_id' => $orderId,
        ]);
    }
}
