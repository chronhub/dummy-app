<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Storm\Contract\Tracker\Listener;
use Storm\Tracker\GenericListener;

use function is_callable;

class ReporterSubscriberMap
{
    /**
     * @var Collection{string, array<ReporterSubscriberAttribute}>
     */
    protected Collection $entries;

    public function __construct(
        protected ReporterSubscriberLoader $loader,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(): void
    {
        $this->entries = $this->loader
            ->getAttributes()
            ->map(fn (ReporterSubscriberAttribute $attribute) => $this->build($attribute));
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    protected function build(ReporterSubscriberAttribute $attribute): ReporterSubscriberHandler
    {

        // todo bind listener
        return $this->newSubscriberInstance($attribute);
    }

    protected function newSubscriberInstance(ReporterSubscriberAttribute $attribute): ReporterSubscriberHandler
    {
        $parameters = $this->makeParametersFromConstructor($attribute->references);

        $instance = $this->app->make($attribute->className, ...$parameters);

        $callback = is_callable($instance) ? $instance : $instance->{$attribute->method}(...);

        $name = $this->formatName($attribute->name, $attribute->className, $attribute->method);

        $listener = $this->toListener($attribute->event, $callback, $attribute->priority);

        return new ReporterSubscriberHandler($name, $attribute->supports, $listener, $attribute->autowire);
    }

    protected function toListener(string $event, callable $instance, int $priority): Listener
    {
        return new GenericListener($event, $instance, $priority);
    }

    protected function formatName(?string $name, string $class, string $method): string
    {
        return $name ?? $class.'@'.$method;
    }

    protected function makeParametersFromConstructor(array $references): array
    {
        $arguments = [];

        foreach ($references as $parameter) {
            foreach ($parameter as [$parameterName, $serviceId]) {
                $arguments[] = [$parameterName => $this->app[$serviceId]];
            }
        }

        return $arguments;
    }
}
