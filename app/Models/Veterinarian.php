<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veterinarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'email', 'phone', 'specialty'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
