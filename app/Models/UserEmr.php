<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Emergency;
class UserEmr extends Model
{
    protected $table = 'user_emergencies';
    protected $fillable = [
        'status',
        'date',
        'user_id',
        'emergency_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }
}
