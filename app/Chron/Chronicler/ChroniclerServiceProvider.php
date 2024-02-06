<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

use App\Chron\Aggregate\AggregateEventReleaser;
use App\Chron\Aggregate\GenericAggregateRepository;
use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Chronicler\Contracts\EventableChronicler;
use App\Chron\Chronicler\Contracts\StreamPersistence;
use App\Chron\Chronicler\Decorator\EventChronicler;
use App\Chron\Chronicler\Decorator\TransactionalEventChronicler;
use App\Chron\EventPublisher\EventPublisher;
use App\Chron\EventPublisher\EventPublisherSubscriber;
use App\Chron\EventPublisher\InMemoryEventPublisher;
use App\Chron\Model\Customer\Repository\CustomerChroniclerRepository;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Order\Repository\OrderAggregateRepository;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Reporter\Decorator\ChainMessageDecorator;
use App\Chron\Reporter\Decorator\EventId;
use App\Chron\Reporter\Decorator\EventTime;
use App\Chron\Reporter\Decorator\EventType;
use App\Chron\Reporter\Subscribers\CorrelationHeaderCommand;
use App\Chron\Serializer\DomainEventSerializer;
use App\Chron\Serializer\StreamEventSerializer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Chronicler\TrackStream;
use Storm\Chronicler\TrackTransactionalStream;
use Storm\Contract\Chronicler\EventStreamProvider as Provider;
use Storm\Serializer\JsonSerializer;
use Storm\Serializer\MessageContentSerializer;
use Storm\Stream\StreamName;

class ChroniclerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        // does not work with resolving
        $this->app->extend(Chronicler::class, function (Chronicler $chronicler): Chronicler {
            // event publisher
            $publisher = $this->app[EventPublisherSubscriber::class];
            $publisher($chronicler);

            // correlation header
            if ($chronicler instanceof EventableChronicler) {
                /** @var CorrelationHeaderCommand $correlation */
                $correlation = $this->app[CorrelationHeaderCommand::class];
                $correlation->attachToChronicler($chronicler);
            }

            return $chronicler;
        });
    }

    public function register(): void
    {
        $this->registerEventDecorators();
        $this->registerStreamEventSerializer();
        $this->registerEventPublisher();
        $this->registerEventStreamProvider();
        $this->registerStreamPersistence();
        $this->registerAggregateRepositories();
        $this->registerDefaultEventStore();

        $this->app->singleton(CorrelationHeaderCommand::class);
    }

    protected function registerDefaultEventStore(): void
    {
        $this->app->singleton(Chronicler::class, fn (): Chronicler => $this->makeTransactionalEventChronicler());
    }

    protected function registerAggregateRepositories(): void
    {
        $this->app->bind(CustomerCollection::class, function (): CustomerCollection {
            $repository = $this->makeGenericAggregateRepository(new StreamName('customer'));

            return new CustomerChroniclerRepository($repository);
        });

        $this->app->bind(OrderList::class, function (): OrderList {
            $repository = $this->makeGenericAggregateRepository(new StreamName('order'));

            return new OrderAggregateRepository($repository);
        });
    }

    protected function makeRealChronicler(): Chronicler
    {
        return new PgsqlTransactionalChronicler(
            $this->app['db.connection'],
            $this->app[Provider::class],
            $this->app[StreamPersistence::class],
            $this->app[CursorConnectionLoader::class],
        );
    }

    protected function makeEventChronicler(): EventableChronicler
    {
        $chronicler = $this->makeRealChronicler();

        return new EventChronicler($chronicler, new TrackStream());
    }

    protected function makeTransactionalEventChronicler(): TransactionalEventChronicler
    {
        $chronicler = $this->makeRealChronicler();

        $streamTracker = new TrackTransactionalStream();

        return new TransactionalEventChronicler($chronicler, $streamTracker);
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
        $this->app->singleton(EventPublisher::class, fn () => new InMemoryEventPublisher());
    }

    private function registerEventStreamProvider(): void
    {
        $this->app->bind(Provider::class, EventStreamProvider::class);
    }

    private function registerStreamPersistence(): void
    {
        $this->app->bind(StreamPersistence::class, StandardStreamPersistence::class);
    }

    private function makeGenericAggregateRepository(StreamName $streamName): GenericAggregateRepository
    {
        return new GenericAggregateRepository(
            $this->app[Chronicler::class],
            $streamName,
            new AggregateEventReleaser($this->app['event.decorator.chain.default'])
        );
    }

    public function provides(): array
    {
        return [
            Chronicler::class,
            EventPublisher::class,
            Provider::class,
            StreamPersistence::class,
            StreamEventSerializer::class,
            'event.decorator.chain.default',
            CustomerCollection::class,
            OrderList::class,
        ];
    }
}
