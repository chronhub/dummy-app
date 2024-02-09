<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler;

use App\Chron\Package\EventPublisher\InMemoryEventPublisher;
use App\Chron\Package\Reporter\Decorator\ChainMessageDecorator;
use App\Chron\Package\Reporter\Decorator\EventId;
use App\Chron\Package\Reporter\Decorator\EventTime;
use App\Chron\Package\Reporter\Decorator\EventType;
use App\Chron\Package\Reporter\Subscribers\CorrelationHeaderCommand;
use App\Chron\Package\Serializer\DomainEventSerializer;
use App\Chron\Package\Serializer\StreamEventSerializer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Serializer\JsonSerializer;
use Storm\Serializer\MessageContentSerializer;

class ChroniclerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        $this->registerEventDecorators();
        $this->registerStreamEventSerializer();
        $this->registerEventPublisher();

        // fixMe: should be handled by a share attribute in stream subscriber
        $this->app->singleton(CorrelationHeaderCommand::class);
    }

    private function registerEventDecorators(): void
    {
        $this->app->bind('event.decorator.chain.default', function (Application $app) {
            return new ChainMessageDecorator(
                new EventId(),
                new EventType(),
                $app[EventTime::class],
            );
        });
    }

    private function registerStreamEventSerializer(): void
    {
        $this->app->bind(StreamEventSerializer::class, function () {
            return new DomainEventSerializer(
                (new JsonSerializer())->create(),
                new MessageContentSerializer()
            );
        });
    }

    private function registerEventPublisher(): void
    {
        // fixMe: can not use $app['reporter.event.default]
        //  subscribers are not attached
        $this->app->singleton('event.publisher.in_memory', fn () => new InMemoryEventPublisher());
    }

    public function provides(): array
    {
        return [
            'event.publisher.in_memory',
            StreamEventSerializer::class,
            'event.decorator.chain.default',
        ];
    }
}
