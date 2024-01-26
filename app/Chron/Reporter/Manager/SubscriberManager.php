<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;
use App\Chron\Reporter\Subscribers\CorrelationHeaderCommand;
use App\Chron\Reporter\Subscribers\FinalizeTransactionalCommand;
use App\Chron\Reporter\Subscribers\RouteMessageSubscriber;
use App\Chron\Reporter\Subscribers\StartTransactionalCommand;
use App\Chron\Reporter\Subscribers\SyncRouteMessageSubscriber;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\Subscriber\HandleCommand;
use Storm\Reporter\Subscriber\HandleEvent;
use Storm\Reporter\Subscriber\HandleQuery;
use Storm\Reporter\Subscriber\MakeMessage;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Support\Message\MessageDecoratorSubscriber;

use function array_merge_recursive;

class SubscriberManager implements ReporterSubscriberManager
{
    protected array $subscribers = [
        'factory' => [100000 => MakeMessage::class],
        'message_decorator' => [98000 => MessageDecoratorSubscriber::class],
        'route_message' => [10000 => RouteMessageSubscriber::class],
        'sync_route_message' => [10000 => SyncRouteMessageSubscriber::class],
        'start_transaction' => [20000 => StartTransactionalCommand::class],
        'correlation' => [15000 => CorrelationHeaderCommand::class],
        'finalize_transaction' => [1000 => FinalizeTransactionalCommand::class],
        'handle_command' => [0 => HandleCommand::class],
        'handle_event' => [0 => HandleEvent::class],
        'handle_query' => [0 => HandleQuery::class],
    ];

    public function get(string $reporterId, DomainType $type): array
    {
        return match ($type) {
            DomainType::COMMAND => $this->commandReporter($reporterId),
            DomainType::QUERY => $this->queryReporter($reporterId),
            DomainType::EVENT => $this->eventReporter($reporterId),
        };
    }

    protected function commandReporter(string $reporterId): array
    {
        $subscribers = [
            Reporter::DISPATCH_EVENT => [
                $this->subscribers['start_transaction'],
                $this->subscribers['correlation'],
                $this->subscribers['route_message'],
                $this->subscribers['handle_command'],
            ],
            Reporter::FINALIZE_EVENT => [
                $this->subscribers['finalize_transaction'],
            ],
            'listeners' => [
                // your string listeners here ...
            ],
        ];

        return array_merge_recursive($subscribers, $this->commons($reporterId));
    }

    protected function eventReporter(string $reporterId): array
    {
        $subscribers = [
            Reporter::DISPATCH_EVENT => [
                $this->subscribers['route_message'],
                $this->subscribers['handle_event'],
            ],
            Reporter::FINALIZE_EVENT => [

            ],
            'listeners' => [
                // your string|Listener instance listeners here ...
            ],
        ];

        return array_merge_recursive($subscribers, $this->commons($reporterId));
    }

    protected function queryReporter(string $reporterId): array
    {
        $subscribers = [
            Reporter::DISPATCH_EVENT => [
                $this->subscribers['sync_route_message'],
                $this->subscribers['handle_query'],
            ],
            Reporter::FINALIZE_EVENT => [

            ],
            'listeners' => [
                // your string|Listener listeners here ...
            ],
        ];

        return array_merge_recursive($subscribers, $this->commons($reporterId));
    }

    protected function commons(string $reporterId): array
    {
        return [
            Reporter::DISPATCH_EVENT => [
                $this->subscribers['factory'],
                [99000 => new NameReporter($reporterId)], // todo pass third param as args for string listener
                $this->subscribers['message_decorator'], //stub wip
            ],

            Reporter::FINALIZE_EVENT => [

            ],

            'listeners' => [
                // your string|Listener listeners here ...
            ],
        ];
    }
}
