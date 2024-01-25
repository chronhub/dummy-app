<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\DomainType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use RuntimeException;
use Storm\Contract\Tracker\Listener;
use Storm\Tracker\GenericListener;

use function array_map;
use function is_string;
use function sprintf;

class ReporterSubscriberResolver
{
    public function __construct(protected Application $app)
    {
    }

    /**
     * @return array<Listener>
     */
    public function make(ReporterAttribute $attribute): array
    {
        $subscribers = array_map(
            fn (callable $resolver): array => $resolver($attribute),
            $this->resolveSubscribers()
        );

        return Arr::flatten($subscribers);
    }

    protected function fromFactory(string $factory, string $reporterId, string $type): array
    {
        $subscribers = $this->app[$factory]->get($reporterId, DomainType::from($type));

        return $this->toListener($subscribers);
    }

    protected function toListener(array $subscribers): array
    {
        $listeners = [];

        foreach ($subscribers as $event => $subscriber) {
            if ($event === 'listeners') {
                $listeners[] = $this->resolveListener($subscriber);
            } else {
                foreach ($subscriber as $listener) {
                    $listeners[] = $this->resolveGenericListener($event, $listener);
                }
            }
        }

        return $listeners;
    }

    /**
     * @return array<GenericListener>
     */
    protected function resolveGenericListener(string $event, array $listeners): array
    {
        $genericListeners = [];

        foreach ($listeners as $priority => $service) {
            if (is_string($service)) {
                $service = $this->app[$service];
            }

            $genericListeners[] = new GenericListener($event, $service, $priority);
        }

        return $genericListeners;
    }

    /**
     * @return array<Listener>
     */
    protected function resolveListener(array $userListeners): array
    {
        $listeners = [];

        foreach ($userListeners as $userListener) {
            if (is_string($userListener)) {
                $userListener = $this->app[$userListener];
            }

            if (! $userListener instanceof Listener) {
                throw new RuntimeException(sprintf('Listener %s must be an instance of %s', $userListener, Listener::class));
            }

            $listeners[] = $userListener;
        }

        return $listeners;
    }

    protected function resolveSubscribers(): array
    {
        return [
            fn (ReporterAttribute $attribute): array => $this->resolveListener($attribute->listeners),
            function (ReporterAttribute $attribute): array {
                return is_string($attribute->subscribers)
                    ? $this->fromFactory($attribute->subscribers, $attribute->id, $attribute->type)
                    : $this->toListener($attribute->subscribers);
            },
        ];
    }
}
