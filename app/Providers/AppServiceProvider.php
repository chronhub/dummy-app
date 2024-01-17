<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Attribute\TagContainer;
use App\Chron\Reporter\Router\MessageRouter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Reporter\Router;
use Storm\Message\MessageServiceProvider;
use Storm\Reporter\Producer\AsyncMessageProducer;
use Storm\Reporter\Producer\SyncMessageProducer;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportEvent;
use Storm\Reporter\ReportQuery;
use Storm\Reporter\Routing;
use Storm\Reporter\Subscriber\AsyncRouteMessage;
use Storm\Reporter\Subscriber\HandleCommand;
use Storm\Reporter\Subscriber\HandleEvent;
use Storm\Reporter\Subscriber\HandleQuery;
use Storm\Reporter\Subscriber\MakeMessage;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Reporter\Subscriber\SyncRouteMessage;
use Storm\Support\Message\MessageDecoratorSubscriber;
use Storm\Tracker\GenericListener;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TagContainer::class);

        $this->app->register(MessageServiceProvider::class);

        $this->app->bind(Router::class, MessageRouter::class);
        $this->app->bind('message.producer.async', AsyncMessageProducer::class);
        $this->app->bind('message.producer.sync', SyncMessageProducer::class);

        $this->registerReporterCommand();
        $this->registerReporterQuery();
        $this->registerReporterEvent();
    }

    public function boot(): void
    {
        $this->app[TagContainer::class]->autoTag();
    }

    private function registerReporterCommand(): void
    {
        $this->app->singleton('reporter.command.default', function (Application $app): ReportCommand {
            $reporter = new ReportCommand();
            $reporter->setContainer($app);

            $event = Reporter::DISPATCH_EVENT;
            $routeSubscriber = new AsyncRouteMessage($app[Routing::class], $app['message.producer.async']);

            $listeners = [
                new GenericListener($event, $app[MakeMessage::class], 100000),
                new GenericListener($event, new NameReporter('reporter.command.default'), 95000),
                new GenericListener($event, $app[MessageDecoratorSubscriber::class], 90000),
                new GenericListener($event, $routeSubscriber, 10000),
                new GenericListener($event, $app[HandleCommand::class], 0),
            ];

            $reporter->subscribe(...$listeners);

            return $reporter;
        });
    }

    private function registerReporterQuery(): void
    {
        $this->app->singleton('reporter.query.default', function (Application $app): ReportQuery {
            $reporter = new ReportQuery();

            $reporter->setContainer($app);

            $event = Reporter::DISPATCH_EVENT;
            $routeSubscriber = new SyncRouteMessage($app[Routing::class], $app['message.producer.sync']);

            $listeners = [
                new GenericListener($event, $app[MakeMessage::class], 100000),
                new GenericListener($event, new NameReporter(ReportQuery::class), 95000),
                new GenericListener($event, $app[MessageDecoratorSubscriber::class], 90000),
                new GenericListener($event, $routeSubscriber, 10000),
                new GenericListener($event, $app[HandleQuery::class], 0),
            ];

            $reporter->subscribe(...$listeners);

            return $reporter;
        });
    }

    private function registerReporterEvent(): void
    {
        $this->app->singleton('reporter.event.default', function (Application $app): ReportEvent {
            $reporter = new ReportEvent();
            $reporter->setContainer($app);

            $event = Reporter::DISPATCH_EVENT;
            $routeSubscriber = new SyncRouteMessage($app[Routing::class], $app['message.producer.sync']);

            $listeners = [
                new GenericListener($event, $app[MakeMessage::class], 100000),
                new GenericListener($event, new NameReporter('reporter.event.default'), 95000),
                new GenericListener($event, $app[MessageDecoratorSubscriber::class], 90000),
                new GenericListener($event, $routeSubscriber, 10000),
                new GenericListener($event, $app[HandleEvent::class], 0),
            ];

            $reporter->subscribe(...$listeners);

            return $reporter;
        });
    }
}
