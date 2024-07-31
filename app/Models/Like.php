<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Like extends Model
{

    protected $fillable = [ 'like_date' ,'user_id' , 'post_id'];
    protected $append = [
        'is_liked'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    protected function isLiked(): Attribute
    {
        return Attribute::make(
            get: function () {
                return (bool) (auth()->user()->id == $this['user_id']);
            }
        );
    }
}
