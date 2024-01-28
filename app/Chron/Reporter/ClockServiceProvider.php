<?php

declare(strict_types=1);

namespace App\Chron\Reporter;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Clock\PointInTime;
use Storm\Contract\Clock\SystemClock;

class ClockServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(SystemClock::class, fn (): SystemClock => new PointInTime());
    }

    public function provides(): array
    {
        return [SystemClock::class];
    }
}
