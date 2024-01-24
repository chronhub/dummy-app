<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AttributeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $autoWire = config('reporter.auto_wire', true);

        if ($autoWire === true) {
            $this->getAttributeContainer()->autoWire();
        }
    }

    public function register(): void
    {
        // fixMe
        //  still need to bind the container to the app
        //  as, it's needed for DetermineQueueHandler
        $this->app->singleton(BindReporterContainer::class);

        $this->app->singleton(AttributeContainer::class);
    }

    public function provides(): array
    {
        return [AttributeContainer::class, BindReporterContainer::class];
    }

    protected function getAttributeContainer(): AttributeContainer
    {
        return $this->app[AttributeContainer::class];
    }
}
