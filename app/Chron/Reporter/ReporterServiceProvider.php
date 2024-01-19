<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\TagContainer;
use App\Chron\Reporter\Router\MessageRouter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Message\MessageProducer;
use Storm\Contract\Reporter\Router;
use Storm\Reporter\Producer\AsyncMessageProducer;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportEvent;
use Storm\Reporter\ReportQuery;

class ReporterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    protected string $configPath = __DIR__.'/../../Chron/Reporter/reporter.php';

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath => config_path('reporter.php')], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath, 'reporter');

        $this->app->singleton(TagContainer::class);

        $this->app->bind(Router::class, MessageRouter::class);

        $this->registerDefaultMessageProducer();

        $this->app->singleton(Manager::class, ReporterManager::class);

        $this->app->alias(Manager::class, Report::REPORTER_ID);

        $this->registerDefaultReporters();

        $this->app[TagContainer::class]->autoTag();
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
        $this->app->bind(MessageProducer::class, AsyncMessageProducer::class);
    }

    public function provides(): array
    {
        return [
            Manager::class,
            Report::REPORTER_ID,
            Router::class,
            MessageProducer::class,
        ];
    }
}
