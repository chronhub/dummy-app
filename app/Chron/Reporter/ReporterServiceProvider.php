<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\BindReporterContainer;
use App\Chron\Attribute\TagHandlerContainer;
use App\Chron\Reporter\Manager\Manager;
use App\Chron\Reporter\Manager\ReporterManager;
use App\Chron\Reporter\Router\MessageRouter;
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

        $this->app->singleton(BindReporterContainer::class);
        $this->app->singleton(TagHandlerContainer::class);

        $this->app->bind(Router::class, MessageRouter::class);

        $this->registerDefaultMessageProducer();

        $this->app->singleton(Manager::class, ReporterManager::class);
        $this->app->alias(Manager::class, Report::REPORTER_ID);

        // attributes
        $this->bindReporters($this->app[BindReporterContainer::class]); //need to be first
        $this->registerTagHandler($this->app[TagHandlerContainer::class]);
    }

    protected function registerDefaultMessageProducer(): void
    {
        $this->app->bind(MessageProducer::class, AsyncMessageProducer::class);
    }

    protected function registerTagHandler(TagHandlerContainer $tagContainer): void
    {
        $tagContainer->autoTag();
    }

    private function bindReporters(BindReporterContainer $container): void
    {
        $container->autoBind();
    }

    public function provides(): array
    {
        return [
            Manager::class,
            Report::REPORTER_ID,
            Router::class,
            MessageProducer::class,
            BindReporterContainer::class,
            TagHandlerContainer::class,
        ];
    }
}
