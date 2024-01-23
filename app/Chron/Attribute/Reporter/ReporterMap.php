<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\DomainType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Storm\Tracker\GenericListener;
use Storm\Tracker\TrackMessage;

use function is_string;
use function sprintf;

class ReporterMap
{
    /**
     * @var Collection<array<string, ReporterAttribute>>
     */
    protected Collection $map;

    /**
     * @var array<string, string>
     */
    protected array $bindings;

    public function __construct(
        protected ReporterLoader $loader,
        protected Application $app
    ) {
        $this->map = new Collection();
    }

    public function load(): void
    {
        $this->loader->getAttributes()->each(fn (ReporterAttribute $attribute) => $this->build($attribute));

        $this->map->each(fn (ReporterAttribute $attribute, string $reporterId) => $this->bind($reporterId, $attribute));
    }

    public function getBindings(): Collection
    {
        return collect($this->bindings);
    }

    public function getEntries(): Collection
    {
        return $this->map;
    }

    protected function build(ReporterAttribute $attribute): void
    {
        if ($this->map->has($attribute->id)) {
            throw new RuntimeException("Reporter $attribute->id already exists.");
        }

        $this->map->put($attribute->id, $attribute);
    }

    protected function bind(string $reporterId, ReporterAttribute $attribute): void
    {
        $this->app->bind($reporterId, fn (): Reporter => $this->newHandlerInstance($attribute));

        $this->bindings[$reporterId] = $attribute->class;
    }

    protected function newHandlerInstance(ReporterAttribute $attribute): Reporter
    {
        $tracker = is_string($attribute->tracker) ? $this->app[$attribute->tracker] : new TrackMessage();

        $class = $attribute->class;

        /** @var Reporter $reporter */
        $reporter = new $class($tracker);

        $subscribers = $this->makeSubscribers($attribute->id, $attribute->subscribers, $attribute->type);

        $reporter->subscribe(...$subscribers);

        return $reporter;
    }

    protected function makeSubscribers(string $reporterId, array|string $subscriber, string $type): array
    {
        $subscribers = [];

        if (is_string($subscriber)) {
            $reporterSubscribers = $this->app[$subscriber]->get($reporterId, DomainType::from($type));

            $subscribers[] = $this->handleSubscribers($reporterSubscribers);
        } else {
            $subscribers[] = $this->handleSubscribers($subscriber);
        }

        return Arr::flatten($subscribers);
    }

    protected function handleSubscribers(array $subscribers): array
    {
        $listeners = [];
        foreach ($subscribers as $event => $subscriber) {
            if ($event === 'listeners') {
                $this->addListeners($subscriber);
            } else {
                foreach ($subscriber as $listener) {
                    foreach ($listener as $priority => $service) {
                        if (is_string($service)) {
                            $service = $this->app[$service];
                        }

                        $listeners[] = new GenericListener($event, $service, $priority);
                    }
                }
            }
        }

        return $listeners;
    }

    protected function addListeners(array $userListeners): array
    {
        $listeners = [];

        foreach ($userListeners as $userListener) {
            $listener = $this->app[$userListener];

            if (! $listener instanceof Listener) {
                throw new RuntimeException(sprintf('Listener %s must be an instance of %s', $userListener, Listener::class));
            }

            $listeners[] = $listener;
        }

        return $listeners;
    }
}
