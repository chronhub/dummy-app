<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Order\AddOrderItem;
use App\Chron\Application\Messaging\Command\Order\CreateOrder;
use App\Chron\Application\Messaging\Command\Order\OwnerRequestsOrderCancellation;
use App\Chron\Model\Order\OrderStatus;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CustomerProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use App\Chron\Projection\Provider\OrderProvider;
use RuntimeException;
use stdClass;
use Symfony\Component\Uid\Uuid;

use function in_array;

final readonly class OrderService
{
    public function __construct(
        private OrderProvider $orderProvider,
        private CustomerProvider $customerProvider,
        private InventoryProvider $inventoryProvider
    ) {
    }

    public function addOrderItem(string $customerId, string $orderId): void
    {
        $order = $this->orderProvider->findOrderOfCustomer($customerId, $orderId);

        if ($order === null) {
            throw new RuntimeException('No order found');
        }

        if ($order->customer_id !== $customerId) {
            throw new RuntimeException('Order does not belong to customer');
        }

        if (in_array($order->status, [OrderStatus::CREATED->value, OrderStatus::MODIFIED->value], true)) {
            $this->makeOrderItem($order);
        } else {
            logger('Order is not in a state to be modified: '.$order->status);
        }
    }

    public function makeOrderForRandomCustomer(): void
    {
        $customer = $this->customerProvider->findRandomCustomer();

        if ($customer === null) {
            throw new RuntimeException('No customer found');
        }

        $order = $this->orderProvider->findCurrentOrderOfCustomer($customer->id);

        if ($order === null) {
            $this->createOrder($customer->id);
        } elseif (in_array($order->status, [OrderStatus::CREATED->value, OrderStatus::MODIFIED->value], true)) {
            $this->makeOrderItem($order);
        } else {
            logger('Order is not in a state to be modified: '.$order->status);
        }
    }

    public function cancelOrderByCustomer(string $orderId, string $customerId): void
    {
        Report::relay(OwnerRequestsOrderCancellation::forOrder($orderId, $customerId));
    }

    public function createOrder(string $customerId): void
    {
        Report::relay(CreateOrder::forCustomer($customerId, Uuid::v4()->jsonSerialize()));
    }

    private function makeOrderItem(stdClass $order): void
    {
        $item = $this->inventoryProvider->findRandomItem();

        if ($item === null) {
            throw new RuntimeException('No inventory item found');
        }

        if ($order->items->isEmpty() || ! $this->isOrderItemAlreadyInOrder($order, $item)) {
            $this->addNewOrderItem($order, $item);
        } else {
            logger('Order item already in order');
        }
    }

    private function addNewOrderItem(stdClass $order, stdClass $inventoryItem): void
    {
        Report::relay(AddOrderItem::forOrder(
            $order->id,
            Uuid::v4()->jsonSerialize(),
            $inventoryItem->id,
            $order->customer_id,
            $inventoryItem->unit_price,
            fake()->numberBetween(1, 3)
        ));
    }

    private function isOrderItemAlreadyInOrder(stdClass $order, stdClass $inventoryItem): bool
    {
        return $order->items->contains(
            fn (stdClass $item) => $item->sku_id === $inventoryItem->id
        );
    }
}
