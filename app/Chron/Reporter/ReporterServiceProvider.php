<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\TagContainer;
use App\Chron\Reporter\Router\MessageRouter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Reporter\Router;
use Storm\Reporter\Producer\AsyncMessageProducer;
use Storm\Reporter\Producer\SyncMessageProducer;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportEvent;
use Storm\Reporter\ReportQuery;

class ReporterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $this->app[TagContainer::class]->autoTag();
    }

    public function register(): void
    {
        $this->app->singleton(TagContainer::class);

        $this->app->bind(Router::class, MessageRouter::class);

        $this->registerDefaultMessageProducer();

        $this->app->singleton(Manager::class, ReporterManager::class);

        $this->app->alias(Manager::class, Report::REPORTER_ID);

        $this->registerDefaultReporters();
    }

    protected function registerDefaultReporters(): void
    {
        $this->app->singleton(
            ReporterManager::REPORTERS_DEFAULT['command'],
            fn (Application $app): ReportCommand => $app[Manager::class]->command()
        );

        $this->app->singleton(
            ReporterManager::REPORTERS_DEFAULT['query'],
            fn (Application $app): ReportQuery => $app[Manager::class]->query()
        );

        $this->app->singleton(
            ReporterManager::REPORTERS_DEFAULT['event'],
            fn (Application $app): ReportEvent => $app[Manager::class]->event()
        );
    }

    protected function registerDefaultMessageProducer(): void
    {
        $this->app->bind('message.producer.async', AsyncMessageProducer::class);

        $this->app->bind('message.producer.sync', SyncMessageProducer::class);
    }

    public function provides(): array
    {
        return [
            Manager::class,
            Report::REPORTER_ID,
            Router::class,
            'message.producer.async',
            'message.producer.sync',
        ];
    }
}
