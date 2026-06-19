<?php

namespace App\Http\Controllers;

use App\Models\ClinicSetting;
use App\Support\ClinicPalettes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        abort_if(auth()->user()?->role === 'veterinarian', 403);

        $settings = ClinicSetting::first();

        return Inertia::render('Settings/Edit', [
            'settings' => $settings
                ? [
                    'name' => $settings->name,
                    'logo_path' => $settings->logo_path,
                    'palette_key' => $settings->palette_key ?: ClinicPalettes::DEFAULT_KEY,
                ]
                : [
                    'name' => 'Mi Veterinaria',
                    'logo_path' => null,
                    'palette_key' => ClinicPalettes::DEFAULT_KEY,
                ],
            'palettes' => ClinicPalettes::all(),
        ]);
    }

    public function update(Request $request)
    {
        abort_if(auth()->user()?->role === 'veterinarian', 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'palette_key' => ['required', 'in:'.implode(',', ClinicPalettes::keys())],
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = ClinicSetting::first() ?? new ClinicSetting();
        $settings->name = $request->name;
        $settings->palette_key = $request->palette_key;

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $request->file('logo')->store('clinic', 'public');
        }

        $settings->save();

        return redirect()->back()->with('status', 'Configuracion clinica actualizada.');
    }
}
