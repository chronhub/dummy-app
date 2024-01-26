<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AttributeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $autoWire = config('reporter.auto_wire', false);

        if ($autoWire === true) {
            $this->getAttributeContainer()->autoWire();
        }
    }

    public function register(): void
    {
        $this->app->singleton(ReporterContainer::class);

        $this->app->singleton(AttributeContainer::class);
    }

    public function provides(): array
    {
        return [AttributeContainer::class, ReporterContainer::class];
    }

    protected function getAttributeContainer(): AttributeContainer
    {
        return $this->app[AttributeContainer::class];
    }
}
