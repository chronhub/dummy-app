<?php

declare(strict_types=1);

namespace App\Providers;

use App\Chron\Attribute\AttributeServiceProvider;
use App\Chron\Reporter\ReporterServiceProvider;
use Illuminate\Support\ServiceProvider;
use Storm\Message\MessageServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AttributeServiceProvider::class);
        $this->app->register(MessageServiceProvider::class);
        $this->app->register(ReporterServiceProvider::class);
    }
}
