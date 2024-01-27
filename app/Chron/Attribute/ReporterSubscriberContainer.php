<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\Subscriber\ReporterSubscriberHandler;
use App\Chron\Attribute\Subscriber\ReporterSubscriberMap;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Tracker\GenericListener;

class ReporterSubscriberContainer
{
    protected array $reporterIds = [];

    public function __construct(
        protected ReporterSubscriberMap $reporterSubscriberMap,
        protected Application $app
    ) {
    }

    public function provides(array $reporters): void
    {
        $this->reporterIds = $reporters;

        $this->reporterSubscriberMap->load();

        $this->whenResolveReporter();
    }

    public function getEntries(): Collection
    {
        return $this->reporterSubscriberMap->getEntries();
    }

    protected function whenResolveReporter(): void
    {
        foreach ($this->reporterIds as $reporterId) {
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
        return $this->reporterSubscriberMap->getEntries()
            ->filter(fn (ReporterSubscriberHandler $handler) => $handler->match($reporter))
            ->map(fn (ReporterSubscriberHandler $handler) => $handler->listener)
            ->toArray();
    }
}
