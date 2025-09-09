<?php

namespace App\Providers;

use App\Models\Contract;
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
                return Contract::selectRaw('DISTINCT contract_year')
                    ->whereNotNull('contract_year')
                    ->orderByDesc('contract_year')
                    ->pluck('contract_year')
                    ->toArray();
            });

            $view->with('availableYears', $availableYears);
        });
    }
}
