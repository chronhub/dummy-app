<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Reporter\Manager\Manager;
use App\Chron\Reporter\Manager\ReporterManager;
use App\Chron\Reporter\Router\MessageRouter;
use App\Chron\Reporter\Router\Routable;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Message\MessageProducer;
use Storm\Contract\Reporter\Router;
use Storm\Reporter\Producer\AsyncMessageProducer;

class ReporterServiceProvider extends ServiceProvider implements DeferrableProvider
{
    protected string $configPath = __DIR__.'/../../Chron/Reporter/reporter.php';

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath => config_path('reporter.php')], 'config');

            $this->commands(config('reporter.console.commands', []));
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath, 'reporter');

        $this->app->bind(Routable::class, MessageRouter::class);
        $this->app->bind(MessageProducer::class, AsyncMessageProducer::class);

        $this->app->singleton(Manager::class, ReporterManager::class);
        $this->app->alias(Manager::class, Report::REPORTER_ID);
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
