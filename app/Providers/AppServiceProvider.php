<?php

declare(strict_types=1);

namespace App\Providers;


use App\Chron\Reporter\CommandRouter;
use App\Chron\Reporter\QueryRouter;
use App\Chron\Reporter\Subscribers\SyncRouteMessage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Message\MessageProducer;
use Storm\Contract\Reporter\Reporter;
use Storm\Message\MessageServiceProvider;
use Storm\Reporter\Producer\AsyncMessageProducer;
use Storm\Reporter\Producer\SyncMessageProducer;
use Storm\Reporter\ReportCommand;
use Storm\Reporter\ReportQuery;
use Storm\Reporter\Routing;
use Storm\Reporter\Subscriber\AsyncRouteMessage;
use Storm\Reporter\Subscriber\HandleCommand;
use Storm\Reporter\Subscriber\HandleQuery;
use Storm\Reporter\Subscriber\MakeMessage;
use Storm\Reporter\Subscriber\NameReporter;
use Storm\Support\Message\MessageDecoratorSubscriber;
use Storm\Tracker\GenericListener;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(MessageServiceProvider::class);


        $this->app->bind(MessageProducer::class, AsyncMessageProducer::class);
        $this->app->bind('message.producer.sync', SyncMessageProducer::class);


        $this->registerReporterCommand();
        $this->registerReporterQuery();
    }

    public function boot(): void
    {
        //
    }

    private function registerReporterCommand(): void
    {
        $this->app->bind(ReportCommand::class);

        $this->app->resolving(ReportCommand::class, function (ReportCommand $reporter, Application $app): void {
            $reporter->setContainer($app);

            $event = Reporter::DISPATCH_EVENT;
            $router = new CommandRouter(app());
            $routing = new Routing($router);
            $routeSubscriber = new AsyncRouteMessage($routing, app(MessageProducer::class));

            $listeners = [
                new GenericListener($event, app(MakeMessage::class), 100000),
                new GenericListener($event, new NameReporter(ReportCommand::class), 95000),
                new GenericListener($event, app(MessageDecoratorSubscriber::class), 90000),
                new GenericListener($event, $routeSubscriber, 10000),
                new GenericListener($event, app(HandleCommand::class), 0),

            ];

            $reporter->subscribe(...$listeners);
        });
    }

    private function registerReporterQuery(): void
    {
        $this->app->bind(ReportQuery::class);

        $this->app->resolving(ReportQuery::class, function (ReportQuery $reporter, Application $app): void {
            $reporter->setContainer($app);

            $event = Reporter::DISPATCH_EVENT;
            $router = new QueryRouter(app());
            $routing = new Routing($router);
            $routeSubscriber = new SyncRouteMessage($routing, app('message.producer.sync'));

            $listeners = [
                new GenericListener($event, app(MakeMessage::class), 100000),
                new GenericListener($event, new NameReporter(ReportQuery::class), 95000),
                new GenericListener($event, app(MessageDecoratorSubscriber::class), 90000),
                new GenericListener($event, $routeSubscriber, 10000),
                new GenericListener($event, app(HandleQuery::class), 0),

            ];

            $reporter->subscribe(...$listeners);
        });
    }
}
