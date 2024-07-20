<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'num_of_attendees', 'date','time'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
