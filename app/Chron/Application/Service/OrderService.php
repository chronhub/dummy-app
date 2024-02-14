<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Chron\Application\Messaging\Command\Order\AddOrderItem;
use App\Chron\Application\Messaging\Command\Order\CancelOrder;
use App\Chron\Application\Messaging\Command\Order\CloseOrder;
use App\Chron\Application\Messaging\Command\Order\CreateOrder;
use App\Chron\Application\Messaging\Command\Order\DeliverOrder;
use App\Chron\Application\Messaging\Command\Order\ModifyOrder;
use App\Chron\Application\Messaging\Command\Order\PayOrder;
use App\Chron\Application\Messaging\Command\Order\RefundOrder;
use App\Chron\Application\Messaging\Command\Order\ReturnOrder;
use App\Chron\Application\Messaging\Command\Order\ShipOrder;
use App\Chron\Package\Reporter\Report;
use App\Chron\Projection\Provider\CustomerProvider;
use App\Chron\Projection\Provider\InventoryProvider;
use App\Chron\Projection\Provider\OrderProvider;
use RuntimeException;
use stdClass;
use Symfony\Component\Uid\Uuid;

final readonly class OrderService
{
    public function __construct(
        private OrderProvider $orderProvider,
        private CustomerProvider $customerProvider,
        private InventoryProvider $inventoryProvider
    ) {
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
        } else {
            $this->makeOrderItem($order);
        }
    }

    private function createOrder(string $customerId): void
    {
        Report::relay(CreateOrder::forCustomer($customerId, Uuid::v4()->jsonSerialize()));
    }

    private function makeOrderItem(stdClass $order): void
    {
        $item = $this->inventoryProvider->findRandomItem();

        if ($item === null) {
            throw new RuntimeException('No inventory item found');
        }

        if ($order->items->isEmpty()) {
            Report::relay(AddOrderItem::forOrder(
                $order->id,
                Uuid::v4()->jsonSerialize(),
                $item->id,
                $item->item_id,
                $order->customer_id,
                $item->unit_price,
                fake()->numberBetween(1, 10)
            ));
        } else {
            logger('Order item not empty');
        }
    }

    //    public function modifyOrder(string $customerId, string $orderId): void
    //    {
    //        $amount = (string) fake()->randomFloat(2, 10, 3000);
    //
    //        Report::relay(ModifyOrder::forCustomer($customerId, $orderId, $amount));
    //    }

    //    public function payOrder(string $customerId, string $orderId): void
    //    {
    //        Report::relay(PayOrder::forCustomer($customerId, $orderId));
    //    }
    //
    //    public function shipOrder(string $customerId, string $orderId): void
    //    {
    //        Report::relay(ShipOrder::forCustomer($customerId, $orderId));
    //    }
    //
    //    public function deliverOrder(string $customerId, string $orderId): void
    //    {
    //        Report::relay(DeliverOrder::forCustomer($customerId, $orderId));
    //    }
    //
    //    public function returnOrder(string $customerId, string $orderId): void
    //    {
    //        if (fake()->numberBetween(1, 100) > 98) {
    //            Report::relay(ReturnOrder::forCustomer($customerId, $orderId));
    //        }
    //    }
    //
    //    public function refundOrder(string $customerId, string $orderId): void
    //    {
    //        Report::relay(RefundOrder::forCustomer($customerId, $orderId));
    //    }
    //
    //    public function cancelOrder(string $customerId, string $orderId): void
    //    {
    //        Report::relay(CancelOrder::forCustomer($customerId, $orderId));
    //    }
    //
    //    public function closeOrder(string $customerId, string $orderId): void
    //    {
    //        Report::relay(CloseOrder::forCustomer($customerId, $orderId));
    //    }
}
