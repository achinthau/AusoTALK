<?php

namespace App\Providers;

use Filament\Actions\Exports\Models\Export;
use App\Policies\ExportPolicy;
use App\Services\AusoApiManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AusoApiManager::class, function ($app) {
            return new AusoApiManager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Export::class, ExportPolicy::class);
    }
}
