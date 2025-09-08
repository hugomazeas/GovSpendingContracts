<?php

namespace App\Providers;

use App\Repositories\Contracts\ProcurementContractRepositoryInterface;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
