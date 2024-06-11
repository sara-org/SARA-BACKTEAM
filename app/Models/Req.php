<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmpReq;

class Req extends Model
{
    protected $guarded =[];
    protected $table = 'requests';
    public function empreq()
    {
        return $this->hasOne(EmpReq::class, 'request_id');
    }
    public function docreq()
    {
        return $this->hasOne(DocReq::class, 'request_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
