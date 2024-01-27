<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use RuntimeException;
use Storm\Contract\Reporter\Reporter;
use Storm\Tracker\TrackMessage;

use function class_exists;
use function is_string;

/**
 * @template TQueue of array{'default_queue': ?array, 'enqueue': string}
 */
class ReporterMap
{
    /**
     * @var Collection<array<string, ReporterAttribute>>
     */
    protected Collection $entries;

    /**
     * @var array<string, TQueue>
     */
    protected array $queues = [];

    public function __construct(
        protected ReporterLoader $loader,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(): void
    {
        $this->loader
            ->getAttributes()
            ->each(function (ReporterAttribute $attribute): void {
                $this->makeEntry($attribute);

                $this->bind($attribute);
            });
    }

    /**
     * @return Collection<array<string, ReporterAttribute>>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    /**
     * Return default queue and enqueue method for message handler
     *
     * @return array<string, TQueue>
     */
    public function getDeclaredQueues(): array
    {
        return $this->entries->mapWithKeys(function (ReporterAttribute $attribute): array {
            $defaultQueue = $attribute->defaultQueue;

            if (is_string($defaultQueue)) {
                $defaultQueue = $this->app[$defaultQueue]->jsonSerialize();
            }

            return [$attribute->id => ['default_queue' => $defaultQueue, 'enqueue' => $attribute->enqueue]];
        })->toArray();
    }

    protected function makeEntry(ReporterAttribute $attribute): void
    {
        if ($this->entries->has($attribute->id)) {
            throw new RuntimeException("Reporter $attribute->id already exists");
        }

        $this->entries->put($attribute->id, $attribute);
    }

    protected function bind(ReporterAttribute $attribute): void
    {
        $this->app->bind($attribute->id, fn (): Reporter => $this->newHandlerInstance($attribute));
    }

    protected function newHandlerInstance(ReporterAttribute $attribute): Reporter
    {
        $reporter = $this->determineReporter($attribute);

        if ($attribute->listeners !== []) {
            $reporter->subscribe(...$attribute->listeners);
        }

        return $reporter;
    }

    protected function determineReporter(ReporterAttribute $attribute): Reporter
    {
        $abstract = $attribute->abstract;

        if (class_exists($abstract)) {
            $tracker = is_string($attribute->tracker) ? $this->app[$attribute->tracker] : new TrackMessage();

            return new $abstract($tracker);
        }

        // fixMe: bound reporter as service is not supported yet
        throw new RuntimeException("Reporter $abstract not found. Bound as service is not supported yet");
    }
}
