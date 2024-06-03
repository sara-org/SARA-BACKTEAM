<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centerimg extends Model
{

    protected $fillable = [ 'photo','centerinfo_id' ];


    public function centerinfo()
    {
        return $this->belongsTo(Centerinfo::class);
    }
}
