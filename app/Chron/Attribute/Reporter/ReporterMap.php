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
        protected ReporterSubscriberResolver $subscriberResolver,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(): void
    {
        $this->loader->getAttributes()->each(function (ReporterAttribute $attribute): void {
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
     * @return array<string, TQueue>
     */
    public function getQueues(): array
    {
        return $this->queues;
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

        $this->provideDefaultQueueIfExists($attribute->id, $attribute->defaultQueue, $attribute->enqueue);
    }

    protected function newHandlerInstance(ReporterAttribute $attribute): Reporter
    {
        $reporter = $this->determineReporter($attribute);

        $subscribers = $this->subscriberResolver->make($attribute);
        $reporter->subscribe(...$subscribers);

        return $reporter;
    }

    protected function determineReporter(ReporterAttribute $attribute): Reporter
    {
        // todo add class to reporter attribute tp make different between class and abstract

        $abstract = $attribute->abstract;

        if (class_exists($attribute->abstract)) {
            $tracker = is_string($attribute->tracker) ? $this->app[$attribute->tracker] : new TrackMessage();

            return new $abstract($tracker);
        }

        if ($abstract === $attribute->id) {
            throw new RuntimeException("Reporter $abstract is already bound under id $attribute->id");
        }

        return $this->app[$abstract];
    }

    protected function provideDefaultQueueIfExists(string $reporterId, ?string $defaultQueue, string $enqueue): void
    {
        if (is_string($defaultQueue)) {
            $defaultQueue = $this->app[$defaultQueue]->jsonSerialize();
        }

        $this->queues[$reporterId] = ['default_queue' => $defaultQueue, 'enqueue' => $enqueue];
    }
}
