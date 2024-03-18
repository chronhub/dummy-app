<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Order;

use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;
use Storm\Message\AbstractDomainCommand;

final class PayOrder extends AbstractDomainCommand
{
    public static function forOrder(string $orderId, string $orderOwner): self
    {
        return new self([
            'order_id' => $orderId,
            'order_owner' => $orderOwner,
        ]);
    }

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function orderOwner(): OrderOwner
    {
        return OrderOwner::fromString($this->content['order_owner']);
    }
}
