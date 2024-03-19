<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Projection\ConnectionProjectionProvider;
use App\Chron\Projection\ConnectionQueryScope;
use App\Chron\Projection\ConnectionSubscriptionFactory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Chronicler\Connection\EventStreamProvider;
use Storm\Contract\Clock\SystemClock;
use Storm\Contract\Projector\ProjectorManagerInterface;
use Storm\Contract\Projector\SubscriptionFactory;
use Storm\Projector\Options\DefaultOption;
use Storm\Projector\ProjectorManager;
use Storm\Serializer\JsonSerializer;

class ProjectorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->registerJsonSerializer();

        $this->registerProviders();

        $this->registerSubscriptionFactory();

        $this->registerProjectorManager();
    }

    public function provides(): array
    {
        return [
            'event_stream.provider.connection',
            'projection.provider.connection',
            ConnectionSubscriptionFactory::class,
            ProjectorManagerInterface::class,
        ];
    }

    private function registerSubscriptionFactory(): void
    {
        $this->app->singleton(ConnectionSubscriptionFactory::class, function (Application $app): SubscriptionFactory {
            return new ConnectionSubscriptionFactory(
                $app['chronicler.event.transactional.standard.pgsql'],
                $app['projection.provider.connection'],
                $app['event_stream.provider.connection'],
                $app[SystemClock::class],
                $app['projection.serializer.json.default'],
                $app[Dispatcher::class],
                $app[ConnectionQueryScope::class],
                new DefaultOption(signal: true)
            );
        });
    }

    private function registerProjectorManager(): void
    {
        $this->app->singleton(ProjectorManagerInterface::class, function (Application $app): ProjectorManagerInterface {
            return new ProjectorManager($app[ConnectionSubscriptionFactory::class]);
        });
    }

    private function registerJsonSerializer(): void
    {
        $this->app->singleton('projection.serializer.json.default', fn () => new JsonSerializer());
    }

    private function registerProviders(): void
    {
        $this->app->bind('event_stream.provider.connection', EventStreamProvider::class);

        $this->app->bind('projection.provider.connection', ConnectionProjectionProvider::class);
    }
}
