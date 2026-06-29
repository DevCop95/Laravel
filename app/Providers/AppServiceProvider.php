<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // El seguimiento publico se accede por token sin autenticacion;
        // limitamos las peticiones por IP para evitar fuerza bruta de tokens.
        RateLimiter::for('public-tracking', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('clinic_settings')) {
            $settings = \App\Models\ClinicSetting::first();
            if ($settings && $settings->name) {
                config(['app.name' => $settings->name]);
            }
        }
    }
}
