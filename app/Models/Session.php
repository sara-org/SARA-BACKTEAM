<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['title','num_of_attendees','date','time'];
    protected $appends = [
        'is_added'
    ];

    public function userSessions()
    {
        return $this->hasMany(UserSession::class);
    }


    protected function isAdded(): Attribute
    {
        return Attribute::make(
            get: function () {
                return ( $this->userSessions()->where('user_id',auth()->id())->exists());
            }
        );
    }

    
}
