<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id', 'session_date'];
    protected $append = [
        'is_Added'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function session()
    {
        return $this->belongsTo(Session::class);
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
