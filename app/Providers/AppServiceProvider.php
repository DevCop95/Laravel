<?php

namespace App\Providers;

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

        if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('clinic_settings')) {
            $settings = \App\Models\ClinicSetting::first();
            if ($settings && $settings->name) {
                config(['app.name' => $settings->name]);
            }
        }
    }
}
