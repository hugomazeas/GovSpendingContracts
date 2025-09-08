<?php

namespace App\Providers;

use App\Repositories\Contracts\ProcurementAnalyticsRepositoryInterface;
use App\Repositories\Contracts\ProcurementContractRepositoryInterface;
use App\Repositories\ProcurementAnalyticsRepository;
use App\Repositories\ProcurementContractRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProcurementContractRepositoryInterface::class,
            ProcurementContractRepository::class
        );

        $this->app->bind(
            ProcurementAnalyticsRepositoryInterface::class,
            ProcurementAnalyticsRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
