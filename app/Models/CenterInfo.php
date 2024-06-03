<?php

namespace App\Models;
use App\Models\Centerimg;
use Illuminate\Database\Eloquent\Model;

class Centerinfo extends Model
{
    protected $fillable = [ 'text' ];


    public function centerimgs()
    {
        return $this->hasMany(Centerimg::class);
    }
}
