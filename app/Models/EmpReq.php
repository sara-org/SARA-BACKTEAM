<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpReq extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'empreqs';
    public function request()
    {
        return $this->belongsTo(Req::class);
    }
}


