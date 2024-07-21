<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Post extends Model
{
    protected $fillable = [ 'text' ,'user_id'];

    protected $append = [
        'is_owner'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    protected function isOwner(): Attribute
    {
        return Attribute::make(
            get: function () {
                return (bool) (auth()->user()->id == $this['user_id']);
            }
        );
    }

    protected function isLiked(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Like::query()->where('user_id', auth()->id())->where('post_id', $this['id'])->exists();
            }
        );
    }
}
