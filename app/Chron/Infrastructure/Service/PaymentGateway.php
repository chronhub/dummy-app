<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Service;

use App\Chron\Model\Order\Balance;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderOwner;

use function sprintf;

final class PaymentGateway
{
    public function process(OrderId $orderId, OrderOwner $orderOwner, Balance $balance): bool
    {
        logger(sprintf('Processing payment for customer %s with order %s for %s', $orderOwner, $orderId->toString(), $balance->value()));

        return true;
    }
}
