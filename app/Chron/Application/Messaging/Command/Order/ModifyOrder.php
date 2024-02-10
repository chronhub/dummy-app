<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use Storm\Message\AbstractDomainCommand;

final class ModifyOrder extends AbstractDomainCommand
{
    public static function forCustomer(string $customerId, string $orderId, string $amount): self
    {
        return new self([
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'amount' => $amount,
        ]);
    }
}
