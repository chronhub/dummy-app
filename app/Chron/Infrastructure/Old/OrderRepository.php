<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Old;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use stdClass;

use function json_decode;
use function json_encode;

readonly class OrderRepository
{
    public function __construct(
        protected Connection $connection,
        protected ApiQuery $apiQuery,
        protected CustomerRepository $customerRepository,
    ) {
    }

    public function createOrder(array $headers): void
    {
        $orderData = $this->createOrderData();

        $data = [
            'stream_name' => $orderData['order_type'],
            'id' => $orderData['order_id'],
            'version' => $orderData['next_version'],
            'type' => 'aggregate_order',
            'metadata' => [
                'event_type' => Str::studly($orderData['order_type']),
            ],
            'content' => json_encode([
                'order_id' => $orderData['order_id'],
                'customer_id' => $orderData['customer_id'],
            ]),
        ];

        $data['metadata'] = json_encode($data['metadata'] + $headers);

        $this->connection->table('stream_event')->insert($data);
    }

    /**
     * @return array{order_id: string, customer_id: string, order_type: string, next_version: int}
     */
    private function createOrderData(): array
    {
        $customer = $this->customerRepository->oneRandomCustomer();

        if (fake()->numberBetween(1, 10) < 3) {
            return $this->getRandomOrderForCustomer($customer->id);
        }

        $order = $this->activeOrderForCustomer($customer->id);

        if ($order === null) {
            return $this->getRandomOrderForCustomer($customer->id);
        }

        return [
            'order_id' => $order->content->order_id,
            'customer_id' => $customer->id,
            'order_type' => $this->getRandomType(),
            'next_version' => $order->version + 1,
        ];
    }

    protected function getRandomType(): string
    {
        return fake()->randomElement([
            'order-fulfillment-canceled', 'order-payment-canceled',
            'order-paid', 'order-cancelled', 'order-fulfilled',
            'order-refunded', 'order-returned', 'order-shipped', 'order-updated',
        ]);
    }

    protected function getRandomOrderForCustomer(string $customerId): array
    {
        return [
            'order_id' => fake()->uuid,
            'customer_id' => $customerId,
            'order_type' => 'order-created',
            'next_version' => 1,
        ];
    }

    public function activeOrderForCustomer(string $customerId): ?stdClass
    {
        try {

            //return $this->apiQuery->getActiveOrderForCustomer($customerId);

            $order = $this->connection
                ->table('order')
                ->whereJsonContains('content->customer_id', $customerId)
                ->orderBy('version', 'desc')
                ->first();

            if ($order === null) {
                return null;
            }

            $order->content = json_decode($order->content, flags: JSON_FORCE_OBJECT);
            $order->metadata = json_decode($order->metadata, flags: JSON_FORCE_OBJECT);

            return $order;
        } catch (QueryException) {
            return null;
        }
    }

    public function activeOrderForCustomerFromView(string $customerId): ?stdClass
    {
        try {
            $order = $this->connection
                ->table('last_version_aggregate_view')
                ->where('type', 'aggregate_order')
                ->where('customer_id', $customerId)
                ->orderBy('version', 'desc')
                ->first();

            if ($order === null) {
                return null;
            }

            $order->content = json_decode($order->content, flags: JSON_FORCE_OBJECT);
            $order->metadata = json_decode($order->metadata, flags: JSON_FORCE_OBJECT);

            return $order;
        } catch (QueryException) {
            return null;
        }
    }
}
