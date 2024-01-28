<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class KernelServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $autoWire = config('reporter.auto_wire', false);

        if ($autoWire === true) {
            $this->getKernel()->bootstraps();
        }
    }

    public function register(): void
    {
        $this->app->singleton(Kernel::class);

        $this->app->bind(Chore::class);
    }

    public function provides(): array
    {
        return [Kernel::class, Chore::class];
    }

    protected function getKernel(): Kernel
    {
        return $this->app[Kernel::class];
    }
}
