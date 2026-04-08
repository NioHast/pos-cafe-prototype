<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\WasteRecord;
use App\Models\MenuIngredient;
use App\Observers\MenuIngredientObserver;
use App\Observers\OrderObserver;
use App\Observers\WasteRecordObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Order::observe(OrderObserver::class);
        WasteRecord::observe(WasteRecordObserver::class);
        MenuIngredient::observe(MenuIngredientObserver::class);

        // Performance optimization
        if (app()->environment('local')) {
            // Disable query logging for better performance in debug mode
            DB::disableQueryLog();
        }
        
        // Enable model caching & optimize queries
        \Illuminate\Database\Eloquent\Model::shouldBeStrict(!app()->isProduction());
        
        // Optimize Eloquent queries - prevent lazy loading issues
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());
    }
}
