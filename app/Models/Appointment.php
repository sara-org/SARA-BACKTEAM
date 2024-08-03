<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Appointment extends Model
{
    protected $guarded = [];
    protected $fillable = [
        'doctor_id',
        'day',
        'reserved_time',
        'date',
        'app_date',
        'status'
    ];
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
