<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Package\Attribute\KernelServiceProvider;
use App\Chron\Package\Chronicler\ChroniclerServiceProvider;
use App\Chron\Package\Reporter\ClockServiceProvider;
use App\Chron\Package\Reporter\Decorator\ChainMessageDecorator;
use App\Chron\Package\Reporter\Decorator\EventDispatched;
use App\Chron\Package\Reporter\Decorator\EventId;
use App\Chron\Package\Reporter\Decorator\EventTime;
use App\Chron\Package\Reporter\Decorator\EventType;
use App\Chron\Package\Reporter\ReporterServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Storm\Contract\Message\MessageFactory;
use Storm\Message\MessageServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        $this->app->register(ClockServiceProvider::class);
        $this->app->register(KernelServiceProvider::class);
        $this->app->register(MessageServiceProvider::class);
        $this->app->register(ReporterServiceProvider::class);
        $this->app->register(ChroniclerServiceProvider::class);
        $this->app->register(ShopServiceProvider::class);

        // to message service provider
        $this->app->alias(MessageFactory::class, 'message.factory.default');
        $this->app->bind('message.decorator.chain.default', function (Application $app) {
            return new ChainMessageDecorator(
                new EventId(),
                new EventType(),
                $app[EventTime::class],
                new EventDispatched()
            );
        });
    }
}
