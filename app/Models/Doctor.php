<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = [
        'age',
        'address',
        'user_id',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
    public function doctimes()
    {
        return $this->hasMany(WorkingHours::class,'doctor_id', 'id');
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
