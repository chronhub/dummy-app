<?php

declare(strict_types=1);

namespace App\Chron\Infrastructure\Old;

use Generator;
use Illuminate\Support\Facades\Http;
use stdClass;
use Symfony\Component\Uid\Uuid;

use function array_map;
use function count;
use function json_decode;
use function json_encode;
use function sprintf;

class ApiQuery
{
    public string $baseUrl = 'host.docker.internal:8080/api/rest/';

    public function getActiveOrderForCustomer(string $customerId): ?stdClass
    {
        $url = sprintf('%s/customer/%s/order', $this->baseUrl, $customerId);

        $data = Http::acceptJson()->get($url)->json('stream_event')[0] ?? null;

        if ($data === null || ! isset($data['stream_event'])) {
            return null;
        }

        return $this->toStdclass($data)[0];
    }

    public function orderFromTo(string $orderId, int $fromVersion, ?int $toVersion): Order
    {
        $url = sprintf('%s/eventstore/order/%s/from/%d/to/%d', $this->baseUrl, $orderId, $fromVersion, $toVersion ?? PHP_INT_MAX);

        $data = Http::acceptJson()->get($url)->json('order');

        $events = $this->normalizeData($data);

        return $this->toAggregateOrder($orderId, $events);
    }

    /**
     * @return array<DomainEvent>
     */
    protected function normalizeData(array $data): array
    {
        return array_map(fn (stdClass $event) => new DomainEvent($event), $this->toStdclass($data));
    }

    /**
     * @return array<stdClass>
     */
    public function toStdclass(array $data): array
    {
        return array_map(fn (array $event) => json_decode(json_encode($event, JSON_FORCE_OBJECT)), $data);
    }

    protected function toAggregateOrder(string $orderId, array $events): Order
    {
        $oId = Uuid::fromString($orderId);

        $order = Order::create($oId);

        $streamEvents = $this->generateEvents($events);

        return $order::reconstitute($oId, $streamEvents);
    }

    protected function generateEvents(array $events): Generator
    {
        yield from $events;

        return count($events);
    }
}
