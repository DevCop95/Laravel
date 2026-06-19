<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\Veterinarian;
use App\Policies\AppointmentPolicy;
use App\Policies\OwnerPolicy;
use App\Policies\PetPolicy;
use App\Policies\ServicePolicy;
use App\Policies\VeterinarianPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Appointment::class => AppointmentPolicy::class,
        Owner::class => OwnerPolicy::class,
        Pet::class => PetPolicy::class,
        Veterinarian::class => VeterinarianPolicy::class,
        Service::class => ServicePolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('admin', fn ($user) => $user->role === 'admin');
    }
}
