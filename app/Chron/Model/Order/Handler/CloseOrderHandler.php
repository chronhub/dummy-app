<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\CloseOrder;
use App\Chron\Infrastructure\Service\CustomerOrderProvider;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Model\Order\Service\CanReturnOrder;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CloseOrder::class,
)]
final readonly class CloseOrderHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerCollection $customers,
        private CustomerOrderProvider $readModel,
        private CanReturnOrder $canReturnOrder,
    ) {
    }

    public function __invoke(CloseOrder $command): void
    {
        $customerId = CustomerId::fromString($command->content['customer_id']);

        if ($this->customers->get($customerId) === null) {
            throw CustomerNotFound::withId($customerId);
        }

        $orderId = OrderId::fromString($command->content['order_id']);

        $order = $this->orders->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withId($orderId);
        }

        $order->close($this->canReturnOrder);

        $this->orders->save($order);

        $this->readModel->close($order->customerId(), $order->orderId(), $order->status(), $order->closedReason());
    }
}
