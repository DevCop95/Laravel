<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'species', 'breed', 'birth_date', 'owner_id', 'veterinarian_id', 'photo_path'];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }
}
