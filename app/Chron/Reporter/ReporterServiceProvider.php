<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use App\Chron\Attribute\BindReporterContainer;
use App\Chron\Attribute\TagHandlerContainer;
use App\Chron\Reporter\Manager\Manager;
use App\Chron\Reporter\Manager\ReporterManager;
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

        $this->registerReporterManager();

        $this->registerDefaultReporters();

        // attributes
        $this->bindReporters($this->app[BindReporterContainer::class]);
        $this->registerTagHandler($this->app[TagHandlerContainer::class]);
    }

    protected function registerDefaultReporters(): void
    {
        $this->app->singleton(
            $this->app[Manager::class]->getDefaultId('command'),
            fn (Application $app): ReportCommand => $app[Manager::class]->command()
        );

        $this->app->singleton(
            $this->app[Manager::class]->getDefaultId('query'),
            fn (Application $app): ReportQuery => $app[Manager::class]->query()
        );

        $this->app->singleton(
            $this->app[Manager::class]->getDefaultId('event'),
            fn (Application $app): ReportEvent => $app[Manager::class]->event()
        );
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

    protected function registerReporterManager(): void
    {
        $this->app->singleton(Manager::class, function (): ReporterManager {
            $manager = new ReporterManager($this->app);

            foreach (['command', 'query', 'event'] as $type) {
                $reporter = config('reporter.reporter.'.$type.'.default');

                if (! blank($reporter)) {
                    $manager->addDefaults($type, 'reporter.'.$type.'.default');
                }
            }

            return $manager;
        });

        $this->app->alias(Manager::class, Report::REPORTER_ID);
    }
}
