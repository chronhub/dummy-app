<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\CustomerRequestsOrderCancellation;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Inventory\Service\InventoryReservationService;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CustomerRequestsOrderCancellation::class,
)]
final readonly class CustomerRequestsOrderCancellationHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerCollection $customers,
        private InventoryReservationService $reservationService,
    ) {
    }

    public function __invoke(CustomerRequestsOrderCancellation $command): void
    {
        $customerId = $command->customerId();

        if ($this->customers->get($customerId) === null) {
            throw CustomerNotFound::withId($customerId);
        }

        $orderId = $command->orderId();

        $order = $this->orders->get($orderId);

        if (! $order instanceof Order) {
            throw OrderNotFound::withId($orderId);
        }

        $order->cancelByCustomer($this->reservationService);

        $this->orders->save($order);
    }
}
