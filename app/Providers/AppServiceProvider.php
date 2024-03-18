<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Application\Console\Shop\SeedShopCommand;
use Illuminate\Support\ServiceProvider;
use Storm\Annotation\Kernel;
use Storm\Support\Providers\StormServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Kernel $kernel */
        $kernel = $this->app[Kernel::class];

        $kernel->boot();
    }

    public function register(): void
    {
        $this->app->register(StormServiceProvider::class);
        $this->app->register(ShopServiceProvider::class);

        $this->commands(SeedShopCommand::class);
    }
}
