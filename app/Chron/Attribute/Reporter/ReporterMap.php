<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use RuntimeException;
use Storm\Contract\Reporter\Reporter;
use Storm\Tracker\TrackMessage;

use function is_string;

/**
 * @template TQueue of array{'queue': array, 'sync': bool}
 */
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

    /**
     * @var array<string, TQueue>
     */
    protected array $queues = [];

    public function __construct(
        protected ReporterLoader $loader,
        protected ReporterSubscriberResolver $subscriberResolver,
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

    /**
     * @return array<string, TQueue>
     */
    public function getQueues(): array
    {
        return $this->queues;
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

        $this->addDefaultQueueForMessageHandler($reporterId, $attribute->defaultQueue, $attribute->sync);
    }

    protected function newHandlerInstance(ReporterAttribute $attribute): Reporter
    {
        $tracker = is_string($attribute->tracker) ? $this->app[$attribute->tracker] : new TrackMessage();

        $reporterClass = $attribute->class;

        /** @var Reporter $reporter */
        $reporter = new $reporterClass($tracker);

        $subscribers = $this->subscriberResolver->make($attribute);

        $reporter->subscribe(...$subscribers);

        return $reporter;
    }

    protected function addDefaultQueueForMessageHandler(string $reporterId, ?string $defaultQueue, bool $sync): void
    {
        if (is_string($defaultQueue)) {
            $defaultQueue = $this->app[$defaultQueue]->jsonSerialize();
        }

        $this->queues[$reporterId] = ['queue' => $defaultQueue, 'sync' => $sync];
    }
}
