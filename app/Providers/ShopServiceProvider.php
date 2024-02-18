<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Infrastructure\Service\CustomerEmailMustBeUnique;
use App\Chron\Infrastructure\Service\OwnerPendingOrderMustBeUnique;
use App\Chron\Model\Customer\Service\UniqueCustomerEmail;
use App\Chron\Model\Order\Service\UniqueOwnerPendingOrder;
use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UniqueCustomerEmail::class, CustomerEmailMustBeUnique::class);
        $this->app->bind(UniqueOwnerPendingOrder::class, OwnerPendingOrderMustBeUnique::class);
    }
}
