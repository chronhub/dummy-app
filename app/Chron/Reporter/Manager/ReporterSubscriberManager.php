<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;
use App\Chron\Reporter\Subscribers\RouteMessageSubscriber;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\Subscriber\HandleCommand;
use Storm\Reporter\Subscriber\HandleEvent;
use Storm\Reporter\Subscriber\HandleQuery;
use Storm\Reporter\Subscriber\MakeMessage;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Support\Message\MessageDecoratorSubscriber;

use function array_merge_recursive;

final class ReporterSubscriberManager implements SubscriberManager
{
    /**
     * @return array{string, array<int, array<int, array<int, int>|string>>}
     */
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
                [0 => HandleCommand::class],
            ],
            Reporter::FINALIZE_EVENT => [

            ],
            'listeners' => [
                // your string listeners here ...
            ],
        ];

        return array_merge_recursive($subscribers, $this->commons(), $this->addNameReporter($reporterId));
    }

    protected function eventReporter(string $reporterId): array
    {
        $subscribers = [
            Reporter::DISPATCH_EVENT => [
                [0 => HandleEvent::class],
            ],
            Reporter::FINALIZE_EVENT => [

            ],
            'listeners' => [
                // your string|Listener instance listeners here ...
            ],
        ];

        return array_merge_recursive($subscribers, $this->commons(), $this->addNameReporter($reporterId));
    }

    protected function queryReporter(string $reporterId): array
    {
        $subscribers = [
            Reporter::DISPATCH_EVENT => [
                [0 => HandleQuery::class],
            ],
            Reporter::FINALIZE_EVENT => [

            ],
            'listeners' => [
                // your string|Listener listeners here ...
            ],
        ];

        return array_merge_recursive($subscribers, $this->commons());
    }

    protected function commons(): array
    {
        return [
            Reporter::DISPATCH_EVENT => [
                [100000 => MakeMessage::class],
                [97000 => MessageDecoratorSubscriber::class], //stub wip
                [10000 => RouteMessageSubscriber::class],
            ],

            Reporter::FINALIZE_EVENT => [

            ],

            'listeners' => [
                // your string|Listener listeners here ...
            ],
        ];
    }

    protected function addNameReporter(string $reporterId): array
    {
        return [Reporter::DISPATCH_EVENT => [[99000 => new NameReporter($reporterId)]]];
    }
}
