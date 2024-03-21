<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Messaging\Command\Order\PayOrder;
use App\Chron\Infrastructure\Service\PaymentGateway;
use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Exception\OrderException;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\Repository\OrderList;

final readonly class OrderPaymentProcess
{
    public function __construct(
        private PaymentGateway $paymentGateway,
        private OrderList $orders,
        private InventoryList $inventory,
    ) {
    }

    public function process(PayOrder $command): void
    {
        $orderId = $command->orderId();

        $order = $this->getOrder($orderId, $command->orderOwner());

        $this->adjustInventory($order);

        $order->pay($this->paymentGateway);

        $this->orders->save($order);
    }

    private function getOrder(OrderId $orderId, OrderOwner $orderOwner): Order
    {
        $order = $this->orders->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withId($orderId);
        }

        if (! $order->owner()->equalsTo($orderOwner)) {
            throw new OrderException('Order does not belong to the customer');
        }

        if ($order->status() !== OrderStatus::CREATED) {
            throw InvalidOrderOperation::withInvalidStatus($orderId, 'pay', $order->status());
        }

        return $order;
    }

    private function adjustInventory(Order $order): void
    {
        /** @var OrderItem $item */
        foreach ($order->items()->getItems() as $item) {
            $inventory = $this->inventory->get($item->skuId);

            $inventory->adjust(PositiveQuantity::create($item->quantity->value));

            $this->inventory->save($inventory);
        }
    }
}
