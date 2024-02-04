<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Aggregate\GenericAggregateRepository;
use App\Chron\Attribute\KernelServiceProvider;
use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Chronicler\Contracts\StreamPersistence;
use App\Chron\Chronicler\CursorConnectionLoader;
use App\Chron\Chronicler\EventStreamProvider;
use App\Chron\Chronicler\PgsqlTransactionalChronicler;
use App\Chron\Chronicler\StandardStreamPersistence;
use App\Chron\EventPublisher\StandardEventPublisher;
use App\Chron\Model\Customer\Repository\CustomerChroniclerRepository;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Reporter\ClockServiceProvider;
use App\Chron\Reporter\Decorator\ChainMessageDecorator;
use App\Chron\Reporter\Decorator\EventDispatched;
use App\Chron\Reporter\Decorator\EventId;
use App\Chron\Reporter\Decorator\EventTime;
use App\Chron\Reporter\Decorator\EventType;
use App\Chron\Reporter\ReporterServiceProvider;
use App\Chron\Serializer\DomainEventSerializer;
use App\Chron\Serializer\StreamEventSerializer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Chronicler\EventStreamProvider as Provider;
use Storm\Contract\Message\MessageFactory;
use Storm\Message\MessageServiceProvider;
use Storm\Serializer\JsonSerializer;
use Storm\Serializer\MessageContentSerializer;
use Storm\Stream\StreamName;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ClockServiceProvider::class);
        $this->app->register(KernelServiceProvider::class);
        $this->app->register(MessageServiceProvider::class);
        $this->app->register(ReporterServiceProvider::class);

        // to message service provider
        $this->app->alias(MessageFactory::class, 'message.factory.default');
        $this->app->bind('message.decorator.chain.default', function (Application $app) {
            return new ChainMessageDecorator(
                new EventId(),
                new EventType(),
                $app[EventTime::class],
                new EventDispatched()
            );
        });

        // stream event decorator
        $this->app->bind('event.decorator.chain.default', function (Application $app) {
            return new ChainMessageDecorator(
                new EventId(),
                new EventType(),
                $app[EventTime::class],
            );
        });

        // stream event serializer
        $this->app->bind(StreamEventSerializer::class, function () {
            return new DomainEventSerializer(
                (new JsonSerializer())->create(),
                new MessageContentSerializer()
            );
        });

        // event stream provider
        $this->app->bind(Provider::class, EventStreamProvider::class);

        // standard stream persistence
        $this->app->bind(StreamPersistence::class, StandardStreamPersistence::class);

        // event store
        $this->app->bind(Chronicler::class, function (Application $app): Chronicler {
            return new PgsqlTransactionalChronicler(
                $app['db.connection'],
                $app[Provider::class],
                $app[StreamPersistence::class],
                $app[CursorConnectionLoader::class],
                new StandardEventPublisher($app['reporter.event.default']),
            );
        });

        // customer collection
        $this->app->bind(CustomerCollection::class, function (Application $app): CustomerCollection {
            $repository = new GenericAggregateRepository(
                $app[Chronicler::class],
                new StreamName('customer'),
                $app['event.decorator.chain.default']
            );

            return new CustomerChroniclerRepository($repository);
        });
    }
}
