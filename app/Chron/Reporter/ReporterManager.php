<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Illuminate\Contracts\Foundation\Application;
use Storm\Contract\Reporter\Reporter;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportEvent;
use Storm\Reporter\ReportQuery;
use Storm\Reporter\Routing;
use Storm\Reporter\Subscriber\AsyncRouteMessage;
use Storm\Reporter\Subscriber\HandleCommand;
use Storm\Reporter\Subscriber\HandleEvent;
use Storm\Reporter\Subscriber\HandleQuery;
use Storm\Reporter\Subscriber\MakeMessage;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Reporter\Subscriber\SyncRouteMessage;
use Storm\Support\Message\MessageDecoratorSubscriber;
use Storm\Tracker\GenericListener;
use Storm\Tracker\TrackMessage;

use function is_string;

class ReporterManager implements Manager
{
    public const REPORTERS_DEFAULT = [
        'command' => 'reporter.command.default',
        'event' => 'reporter.event.default',
        'query' => 'reporter.query.default',
    ];

    protected array $reporters = [];

    public function __construct(protected Application $app)
    {
    }

    public function create(string $name, string|DomainType $type): Reporter
    {
        if (is_string($type)) {
            $type = DomainType::from($type);
        }

        if (isset($this->reporters[$name])) {
            return $this->reporters[$name];
        }

        return $this->reporters[$name] ??= $this->resolve($name, $type);
    }

    public function command(?string $name = null): Reporter
    {
        return $this->create($name ?? self::REPORTERS_DEFAULT['command'], DomainType::COMMAND);
    }

    public function event(?string $name = null): Reporter
    {
        return $this->create($name ?? self::REPORTERS_DEFAULT['event'], DomainType::EVENT);
    }

    public function query(?string $name = null): Reporter
    {
        return $this->create($name ?? self::REPORTERS_DEFAULT['query'], DomainType::QUERY);
    }

    public static function getDefaultId(string $type): string
    {
        return self::REPORTERS_DEFAULT[$type];
    }

    protected function resolve(string $name, DomainType $type): Reporter
    {
        // need concrete and tracker from config
        $concrete = match ($type) {
            DomainType::COMMAND => ReportCommand::class,
            DomainType::EVENT => ReportEvent::class,
            DomainType::QUERY => ReportQuery::class,
        };

        $tracker = new TrackMessage();

        // todo: add name to reporter and add subscriber to tracker NameReporterSubscriber
        $reporter = new $concrete($tracker);
        $reporter->setContainer($this->app);

        $this->addSubscribers($reporter, $type, $name);

        return $reporter;
    }

    protected function addSubscribers(Reporter $reporter, DomainType $type, string $name): void
    {
        $this->subscribeToCommonSubscriber($reporter, $name);

        match ($type) {
            DomainType::COMMAND => $this->addSubscriberToCommand($reporter, $name),
            DomainType::EVENT => $this->addSubscriberToEvent($reporter, $name),
            DomainType::QUERY => $this->addSubscriberToQuery($reporter, $name),
        };
    }

    protected function addSubscriberToCommand(Reporter $reporter, string $name): void
    {
        $event = Reporter::DISPATCH_EVENT;
        $routeSubscriber = new AsyncRouteMessage($this->app[Routing::class], $this->app['message.producer.async']);

        $listeners = [
            new GenericListener($event, $routeSubscriber, 10000),
            new GenericListener($event, $this->app[HandleCommand::class], 0),
        ];

        $reporter->subscribe(...$listeners);
    }

    protected function addSubscriberToEvent(Reporter $reporter, string $name): void
    {
        $event = Reporter::DISPATCH_EVENT;
        $routeSubscriber = new SyncRouteMessage($this->app[Routing::class], $this->app['message.producer.sync']);

        $listeners = [
            new GenericListener($event, $routeSubscriber, 10000),
            new GenericListener($event, $this->app[HandleEvent::class], 0),
        ];

        $reporter->subscribe(...$listeners);
    }

    protected function addSubscriberToQuery(Reporter $reporter, string $name): void
    {
        $event = Reporter::DISPATCH_EVENT;
        $routeSubscriber = new SyncRouteMessage($this->app[Routing::class], $this->app['message.producer.sync']);

        $listeners = [
            new GenericListener($event, $routeSubscriber, 10000),
            new GenericListener($event, $this->app[HandleQuery::class], 0),
        ];

        $reporter->subscribe(...$listeners);
    }

    protected function subscribeToCommonSubscriber(Reporter $reporter, string $name): void
    {
        $event = Reporter::DISPATCH_EVENT;

        $listeners = [
            new GenericListener($event, $this->app[MakeMessage::class], 100000),
            new GenericListener($event, new NameReporter($name), 95000),
            new GenericListener($event, $this->app[MessageDecoratorSubscriber::class], 90000),
        ];

        $reporter->subscribe(...$listeners);
    }
}
