<?php

namespace App\Providers;

use App\Repositories\ContractRepository;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Contracts\ProcurementAnalyticsRepositoryInterface;
use App\Repositories\ProcurementAnalyticsRepository;
use App\Repositories\TimelineRepository;
use App\Repositories\TimelineRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ContractRepositoryInterface::class,
            ContractRepository::class
        );

        $this->app->bind(
            ProcurementAnalyticsRepositoryInterface::class,
            ProcurementAnalyticsRepository::class
        );

        $this->app->bind(
            TimelineRepositoryInterface::class,
            TimelineRepository::class
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
