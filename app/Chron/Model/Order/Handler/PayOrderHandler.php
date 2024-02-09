<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\PayOrder;
use App\Chron\Infrastructure\Service\CustomerOrderProvider;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: PayOrder::class,
)]
final readonly class PayOrderHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerCollection $customers,
        private CustomerOrderProvider $readModel,
        // todo PaymentGateway $paymentGateway
    ) {
    }

    public function __invoke(PayOrder $command): void
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

        $order->pay($orderId, $customerId);

        $this->orders->save($order);

        $this->readModel->update($order->customerId(), $order->orderId(), $order->status());
    }
}
