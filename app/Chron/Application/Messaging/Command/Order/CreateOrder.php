<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;
use Storm\Message\AbstractDomainCommand;

final class CreateOrder extends AbstractDomainCommand
{
    public static function forCustomer(string $orderOwner, string $orderId): self
    {
        return new self([
            'order_owner' => $orderOwner,
            'order_id' => $orderId,
        ]);
    }

    public function orderOwner(): OrderOwner
    {
        return OrderOwner::fromString($this->content['order_owner']);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }
}
