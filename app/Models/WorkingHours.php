<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day',
        'start_time',
        'end_time',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'work_id');
    }
}
