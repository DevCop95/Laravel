<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pet_id',
        'veterinarian_id',
        'service_id',
        'service_price',
        'appointment_date',
        'reason',
        'status',
        'public_token',
        'doctor_notes',
        'service_finished_at',
        'expires_at',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'service_finished_at' => 'datetime',
        'expires_at' => 'datetime',
        'service_price' => 'decimal:2',
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
