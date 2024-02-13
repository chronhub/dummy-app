<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Application\Service\UniqueCustomerEmail;
use App\Chron\Model\Customer\Service\UniqueEmail;
use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UniqueEmail::class, UniqueCustomerEmail::class);
    }
}
