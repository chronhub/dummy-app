<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;
use App\Chron\Reporter\Subscribers\RouteMessageSubscriber;
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
                [10000 => RouteMessageSubscriber::class],
                [0 => HandleCommand::class],
            ],
            Reporter::FINALIZE_EVENT => [

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
                [10000 => RouteMessageSubscriber::class],
                [0 => HandleEvent::class],
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
                [10000 => SyncRouteMessageSubscriber::class],
                [0 => HandleQuery::class],
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
                [100000 => MakeMessage::class],
                [98000 => MessageDecoratorSubscriber::class], //stub wip
                [99000 => new NameReporter($reporterId)],
            ],

            Reporter::FINALIZE_EVENT => [

            ],

            'listeners' => [
                // your string|Listener listeners here ...
            ],
        ];
    }
}
