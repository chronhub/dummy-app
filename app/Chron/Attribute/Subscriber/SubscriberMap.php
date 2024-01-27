<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Storm\Contract\Tracker\Listener;
use Storm\Tracker\GenericListener;

class SubscriberMap
{
    /**
     * @var Collection{string, array<SubscriberAttribute}>
     */
    protected Collection $entries;

    public function __construct(
        protected SubscriberLoader $loader,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(): void
    {
        $this->entries = $this->loader
            ->getAttributes()
            ->map(fn (SubscriberAttribute $attribute) => $this->build($attribute));
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    protected function build(SubscriberAttribute $attribute): SubscriberHandler
    {
        $name = $this->formatName($attribute->name, $attribute->className, $attribute->method);

        $listener = $this->makeListener($attribute);

        return new SubscriberHandler($name, $attribute->supports, $listener, $attribute->autowire);
    }

    protected function makeListener(SubscriberAttribute $attribute): Listener
    {
        $parameters = $this->makeParametersFromConstructor($attribute->references);

        $instance = $this->app->make($attribute->className, ...$parameters);

        $instance = ($attribute->method === '__invoke') ? $instance : $instance->{$attribute->method}(...);

        return new GenericListener($attribute->event, $instance, $attribute->priority);
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
