<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider
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
        // Register Blade directives for currency formatting
        Blade::directive('currency', function ($expression) {
            return "<?php echo App\Helpers\CurrencyFormatter::format({$expression}); ?>";
        });

        Blade::directive('currencyAvg', function ($expression) {
            return "<?php echo App\Helpers\CurrencyFormatter::formatAverage({$expression}); ?>";
        });
    }
}
