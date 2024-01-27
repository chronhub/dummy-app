<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Subscriber\SubscriberHandler;
use App\Chron\Attribute\Subscriber\SubscriberMap;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Tracker\GenericListener;

class Subscribers
{
    public function __construct(
        protected SubscriberMap $subscriberMap,
        protected Application $app
    ) {
    }

    public function bootstrap(array $reporters): void
    {
        $this->subscriberMap->load();

        $this->whenResolveReporter($reporters);
    }

    public function getEntries(): Collection
    {
        return $this->subscriberMap->getEntries();
    }

    protected function whenResolveReporter(array $reporterIds): void
    {
        foreach ($reporterIds as $reporterId) {
            $this->app->resolving($reporterId, function (Reporter $reporter) use ($reporterId) {
                $listeners = $this->resolveSubscribers($reporterId);

                foreach ($listeners as $subscriber) {
                    $reporter->subscribe($subscriber);
                }

                // todo how to deal with this
                $reporter->subscribe(new GenericListener(Reporter::DISPATCH_EVENT, new NameReporter($reporterId), 99000));
            });
        }
    }

    /**
     * @return array<Listener>
     */
    protected function resolveSubscribers(string $reporter): array
    {
        return $this->subscriberMap->getEntries()
            ->filter(fn (SubscriberHandler $handler) => $handler->match($reporter))
            ->map(fn (SubscriberHandler $handler) => $handler->listener)
            ->toArray();
    }
}
