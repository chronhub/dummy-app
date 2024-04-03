<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Application\Console\Shop\MigrateShopCommand;
use App\Chron\Application\Console\Shop\SeedShopCommand;
use App\Console\CartItemReadModelCommand;
use App\Console\CartReadModelCommand;
use App\Console\CatalogReadModelCommand;
use App\Console\CustomerReadModelCommand;
use App\Console\Emit\OrderEmitterCommand;
use App\Console\InventoryReadModelCommand;
use App\Console\OrderItemReadModelCommand;
use App\Console\OrderReadModelCommand;
use App\Console\ProductReadModelCommand;
use App\Console\QueueWorkerCommand;
use App\Console\ReadModelProcess;
use App\Console\ReadReservationCommand;
use App\Console\ResetProjectionCommand;
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

        $this->commands([
            // App

            QueueWorkerCommand::class,
            ReadModelProcess::class,

            // App console
            ReadReservationCommand::class,
            MigrateShopCommand::class,
            SeedShopCommand::class,
            ResetProjectionCommand::class,
            ProductReadModelCommand::class,
            CustomerReadModelCommand::class,
            CatalogReadModelCommand::class,
            InventoryReadModelCommand::class,
            CartReadModelCommand::class,
            CartItemReadModelCommand::class,
            OrderReadModelCommand::class,
            OrderItemReadModelCommand::class,

            //
            OrderEmitterCommand::class,
        ]);
    }
}
