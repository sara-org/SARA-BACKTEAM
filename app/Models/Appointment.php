<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Appointment extends Model
{
    protected $guarded = [];
    protected $fillable = [
        'work_id',
        'date',
    ];

    public function doctimes()
    {
        return $this->belongsTo(WorkingHours::class,'work_id');
    }
}
