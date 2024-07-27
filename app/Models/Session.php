<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'num_of_attendees', 'date','time'];
    protected $append = [
        'is_Added'
    ];

    public function userSessions()
    {
        return $this->hasMany(UserSession::class);
    }


    protected function isAdded(): Attribute
    {
        return Attribute::make(
            get: function () {
                return (bool) (auth()->user()->id == $this['user_id']);
            }
        );
    }
}
