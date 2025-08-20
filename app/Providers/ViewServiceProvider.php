<?php

namespace App\Providers;

use App\Models\ProcurementContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $availableYears = Cache::remember('global_available_years', 3600, function () {
                return ProcurementContract::selectRaw('DISTINCT contract_year')
                    ->whereNotNull('contract_year')
                    ->orderByDesc('contract_year')
                    ->pluck('contract_year')
                    ->toArray();
            });

            $view->with('availableYears', $availableYears);
        });
    }
}
