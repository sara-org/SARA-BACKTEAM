<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'num_of_attendees', 'date'];


    public function usersessions()
    {
        return $this->hasMany(UserSession::class);
    }
}
