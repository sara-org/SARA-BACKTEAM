<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = ['date', 'description', 'doctor_id', 'animal_id'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}