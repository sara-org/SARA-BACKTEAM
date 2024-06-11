<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocReq extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'amount', 'medicine','status'];
    public function request()
    {
        return $this->belongsTo(Req::class);
    }
}


