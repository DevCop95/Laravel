<?php

namespace App\Http\Middleware;

use App\Support\ClinicPalettes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $defaultClinic = [
            'name' => 'Mi Veterinaria',
            'logo_path' => null,
            'palette_key' => ClinicPalettes::DEFAULT_KEY,
            'palette' => ClinicPalettes::resolve(ClinicPalettes::DEFAULT_KEY),
        ];

        $clinic = $defaultClinic;

        if (Schema::hasTable('clinic_settings')) {
            $settings = \App\Models\ClinicSetting::first();

            if ($settings) {
                $palette = ClinicPalettes::resolve($settings->palette_key);
                $clinic = [
                    'name' => $settings->name,
                    'logo_path' => $settings->logo_path,
                    'palette_key' => $palette['key'],
                    'palette' => $palette,
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'clinic' => $clinic,
            'demo' => [
                'enabled' => (bool) config('app.demo'),
                'readonly' => (bool) config('app.demo_readonly'),
            ],
        ];
    }
}
