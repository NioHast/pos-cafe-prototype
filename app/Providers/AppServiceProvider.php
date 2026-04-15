<?php

namespace App\Providers;

use App\Models\MenuIngredient;
use App\Models\WasteRecord;
use App\Observers\MenuIngredientObserver;
use App\Observers\WasteRecordObserver;
use Illuminate\Support\ServiceProvider;

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
        MenuIngredient::observe(MenuIngredientObserver::class);
        WasteRecord::observe(WasteRecordObserver::class);
    }
}
