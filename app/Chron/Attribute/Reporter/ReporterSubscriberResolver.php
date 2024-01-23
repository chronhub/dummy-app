<?php

declare(strict_types=1);

namespace App\Chron\Attribute\Reporter;

use App\Chron\Reporter\DomainType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use RuntimeException;
use Storm\Contract\Tracker\Listener;
use Storm\Tracker\GenericListener;

use function is_string;
use function sprintf;

class ReporterSubscriberResolver
{
    public function __construct(protected Application $app)
    {
    }

    public function make(ReporterAttribute $reporterAttribute): array
    {
        $subscribers = [];

        $subscribers[] = $this->resolveListeners($reporterAttribute->listeners);

        $factory = $reporterAttribute->subscribers;

        if (is_string($factory)) {
            $reporterSubscribers = $this->app[$factory]->get($reporterAttribute->id, DomainType::from($reporterAttribute->type));
            $subscribers[] = $this->transformToListener($reporterSubscribers);
        } else {
            $subscribers[] = $this->transformToListener($factory);
        }

        return Arr::flatten($subscribers);
    }

    protected function transformToListener(array $subscribers): array
    {
        $listeners = [];

        foreach ($subscribers as $event => $subscriber) {
            if ($event === 'listeners') {
                $this->resolveListeners($subscriber);
            } else {
                foreach ($subscriber as $listener) {
                    $listeners[] = $this->resolveGenericListeners($event, $listener);
                }
            }
        }

        return $listeners;
    }

    protected function resolveGenericListeners(string $event, array $listeners): array
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

    protected function resolveListeners(array $userListeners): array
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
}
