<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VeterinarianController;
use App\Http\Controllers\VeterinaryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware('throttle:public-tracking')->group(function () {
    Route::get('/seguimiento/{token}', [VeterinaryController::class, 'showTracking'])
        ->name('tracking.show');
    Route::get('/seguimiento/{token}/data', [VeterinaryController::class, 'trackingData'])
        ->name('tracking.data');
    Route::get('/seguimiento/{token}/imprimir', [VeterinaryController::class, 'printSummary'])
        ->name('tracking.print');
});

Route::get('/dashboard', [VeterinaryController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'demo.readonly'])->group(function () {
    Route::get('/citas', [VeterinaryController::class, 'appointments'])
        ->name('appointments.index');
    Route::get('/pacientes', [VeterinaryController::class, 'pets'])
        ->name('pets.index');
    Route::get('/veterinarios', [VeterinaryController::class, 'veterinarians'])
        ->name('veterinarians.index');
    Route::get('/servicios', [VeterinaryController::class, 'services'])
        ->name('services.index');
    Route::get('/responsables', [VeterinaryController::class, 'owners'])
        ->name('owners.index');
    Route::get('/pacientes/{pet}/historial', [VeterinaryController::class, 'petHistory'])
        ->name('pets.history');
    Route::get('/citas/exportar', [VeterinaryController::class, 'exportAppointmentsCsv'])
        ->name('appointments.export');
    Route::get('/auditoria', [VeterinaryController::class, 'auditLog'])
        ->name('audit.index');

    Route::post('/pets', [PetController::class, 'store'])
        ->name('pets.store');
    Route::patch('/pets/{pet}', [PetController::class, 'update'])
        ->name('pets.update');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])
        ->name('pets.destroy');

    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->name('appointments.store');
    Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update'])
        ->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])
        ->name('appointments.destroy');
    Route::patch('/owners/{owner}', [OwnerController::class, 'update'])
        ->name('owners.update');
    Route::post('/owners', [OwnerController::class, 'store'])
        ->name('owners.store');
    Route::delete('/owners/{owner}', [OwnerController::class, 'destroy'])
        ->name('owners.destroy');
    Route::post('/veterinarians', [VeterinarianController::class, 'store'])
        ->name('veterinarians.store');
    Route::patch('/veterinarians/{veterinarian}', [VeterinarianController::class, 'update'])
        ->name('veterinarians.update');
    Route::delete('/veterinarians/{veterinarian}', [VeterinarianController::class, 'destroy'])
        ->name('veterinarians.destroy');
    Route::post('/services', [ServiceController::class, 'store'])
        ->name('services.store');
    Route::patch('/services/{service}', [ServiceController::class, 'update'])
        ->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
        ->name('services.destroy');

    Route::get('/inventario', [InventoryController::class, 'index'])
        ->name('inventory.index');
    Route::post('/inventario', [InventoryController::class, 'store'])
        ->name('inventory.store');
    Route::patch('/inventario/{item}', [InventoryController::class, 'update'])
        ->name('inventory.update');
    Route::delete('/inventario/{item}', [InventoryController::class, 'destroy'])
        ->name('inventory.destroy');
});

Route::middleware(['auth', 'demo.readonly'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
