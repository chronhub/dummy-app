<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Manager;

use App\Chron\Reporter\DomainType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use RuntimeException;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Storm\Contract\Tracker\MessageTracker;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportEvent;
use Storm\Reporter\ReportQuery;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Tracker\GenericListener;
use Storm\Tracker\TrackMessage;

use function is_string;
use function method_exists;
use function sprintf;

abstract class AbstractReporterManager implements Manager
{
    protected array $config;

    public function __construct(
        protected Application $app,
        array $config = []
    ) {
        $this->config = $config === [] ? $this->app['config']->get('reporter') : $config;
    }

    protected function resolve(string $reporterId, DomainType $type): Reporter
    {
        $config = Arr::get($this->config, $reporterId);

        if (blank($config)) {
            throw new RuntimeException(sprintf('Reporter config not found for name %s', $reporterId));
        }

        $reporter = $this->makeReporter($type, $config);

        $this->addCommonSubscriber($reporter, $reporterId);
        $this->addSubscribers($reporter, $config['subscribers'] ?? []);

        return $reporter;
    }

    protected function addSubscribers(Reporter $reporter, array $config): void
    {
        foreach ($config as $event => $subscribers) {
            if ($event === 'listeners') {
                $this->addListeners($subscribers, $reporter);
            } else {
                $this->addGenericListeners($event, $subscribers, $reporter);
            }
        }
    }

    protected function addCommonSubscriber(Reporter $reporter, string $name): void
    {
        $defaultSubscribers = Arr::get($this->config, 'subscribers', []);

        $this->addSubscribers($reporter, $defaultSubscribers);

        $nameReporter = new NameReporter($name);
        $reporter->subscribe(new GenericListener(Reporter::DISPATCH_EVENT, $nameReporter, 99000));
    }

    protected function makeReporter(DomainType $type, array $config): Reporter
    {
        $concrete = $this->determineReporterClass($type, $config);
        $tracker = $this->makeTracker($config);

        $reporter = new $concrete($tracker);

        if (method_exists($reporter, 'setContainer')) {
            $reporter->setContainer($this->app);
        }

        return $reporter;
    }

    protected function determineReporterClass(DomainType $type, array $config): string
    {
        $concrete = $config['class'] ?? null;

        if ($concrete === null) {
            $concrete = match ($type) {
                DomainType::COMMAND => ReportCommand::class,
                DomainType::EVENT => ReportEvent::class,
                DomainType::QUERY => ReportQuery::class,
            };
        }

        return $concrete;
    }

    protected function makeTracker(array $config): MessageTracker
    {
        $tracker = $config['tracker'] ?? null;

        if (! is_string($tracker)) {
            return new TrackMessage();
        }

        return $this->app[$tracker];
    }

    protected function addListeners(array $listeners, Reporter $reporter): void
    {
        foreach ($listeners as $subscriber) {
            $listener = $this->app[$subscriber];

            if (! $listener instanceof Listener) {
                throw new RuntimeException(sprintf('Common subscriber %s must be an instance of %s', $subscriber, Listener::class));
            }

            $reporter->subscribe($listener);
        }
    }

    protected function addGenericListeners(string $event, array $subscribers, Reporter $reporter): void
    {
        foreach ($subscribers as [$listener, $priority]) {
            $listener = new GenericListener($event, $this->app[$listener], $priority);

            $reporter->subscribe($listener);
        }
    }
}
