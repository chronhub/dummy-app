<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Subscriber;

use App\Chron\Attribute\Reference\ReferenceResolverTrait;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Tracker\GenericListener;

class SubscriberMap
{
    use ReferenceResolverTrait;

    /**
     * @var Collection{string, array<SubscriberAttribute>}
     */
    protected Collection $entries;

    public function __construct(
        protected SubscriberLoader $loader,
        protected Application $app
    ) {
        $this->entries = new Collection();
    }

    public function load(array $reporters): void
    {
        $this->entries = $this->loader
            ->getAttributes()
            ->map(fn (SubscriberAttribute $attribute) => $this->build($attribute));

        $this->whenResolveReporter($reporters);
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    protected function build(SubscriberAttribute $attribute): SubscriberHandler
    {
        $alias = $this->formatAlias($attribute->alias, $attribute->className, $attribute->method);

        $listener = $this->makeListener($attribute);

        return new SubscriberHandler($alias, $attribute->supports, $listener, $attribute->autowire);
    }

    protected function formatAlias(?string $alias, string $class, string $method): string
    {
        // todo deal with multiple reporters, as they should be bound
        return $alias ?? $class.'@'.$method;
    }

    protected function makeListener(SubscriberAttribute $attribute): Listener
    {
        $parameters = $this->makeParametersFromConstructor($attribute->references);

        $instance = $this->app->make($attribute->className, ...$parameters);

        $instance = ($attribute->method === '__invoke') ? $instance : $instance->{$attribute->method}(...);

        return new GenericListener($attribute->event, $instance, $attribute->priority);
    }

    protected function whenResolveReporter(array $reporterIds): void
    {
        foreach ($reporterIds as $reporterId) {
            $this->app->resolving($reporterId, function (Reporter $reporter) use ($reporterId) {
                $listeners = $this->resolveSubscribers($reporterId);

                foreach ($listeners as $subscriber) {
                    $reporter->subscribe($subscriber);
                }

                // todo where to deal with this
                $reporter->subscribe(new GenericListener(Reporter::DISPATCH_EVENT, new NameReporter($reporterId), 99000));
            });
        }
    }

    /**
     * @return array<Listener>
     */
    protected function resolveSubscribers(string $reporter): array
    {
        return $this->getEntries()
            ->filter(fn (SubscriberHandler $handler) => $handler->match($reporter))
            ->map(fn (SubscriberHandler $handler) => $handler->listener)
            ->toArray();
    }

    protected function app(string $serviceId): mixed
    {
        return $this->app[$serviceId];
    }
}
