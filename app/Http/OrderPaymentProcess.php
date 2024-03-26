<?php

declare(strict_types=1);

namespace App\Http;

use App\Chron\Application\Messaging\Command\Order\PayOrder;
use App\Chron\Infrastructure\Service\PaymentGateway;
use App\Chron\Model\Inventory\PositiveQuantity;
use App\Chron\Model\Inventory\Repository\InventoryList;
use App\Chron\Model\Order\Exception\InvalidOrderOperation;
use App\Chron\Model\Order\Exception\OrderException;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\OrderItem;
use App\Chron\Model\Order\OrderOwner;
use App\Chron\Model\Order\OrderStatus;
use App\Chron\Model\Order\Repository\OrderList;
use RuntimeException;
use Throwable;

use function sprintf;
use function usleep;

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
        $order = $this->getOrder($command->orderId(), $command->orderOwner());

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
            //Report::relay(AdjustInventoryItem::withItem($item->skuId->toString(), $item->quantity->value));
            $this->handleAdjust($item);
        }
    }

    private function handleAdjust(OrderItem $orderItem): void
    {
        $retryCount = 0;
        $maxRetries = 20;

        $exception = null;
        while ($retryCount < $maxRetries) {
            $inventory = $this->inventory->get($orderItem->skuId);

            if ($inventory === null) {
                throw new RuntimeException(sprintf('Inventory item with skuId %s not found', $orderItem->skuId->toString()));
            }

            try {
                $inventory->adjust(PositiveQuantity::create($orderItem->quantity->value));

                $this->inventory->save($inventory);

                break;
            } catch (Throwable $e) {
                logger('concurrency exception caught:'.$e->getMessage());
                $exception = $e;

                $retryCount++;
            }

            usleep(1000);
        }

        if ($retryCount === $maxRetries) {
            throw new RuntimeException('Failed to adjust inventory after maximum retries', 0, $exception);
        }
    }
}
