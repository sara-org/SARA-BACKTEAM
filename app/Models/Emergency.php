<?php

namespace App\Models;
use App\Models\User;
use App\Models\UserEmr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emergency extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'description',
        'contact',
        'photo',
        'user_id',
        'emr_date',
        'status',
       ];
       public function userEmergencies()
    {
        return $this->hasOne(UserEmr::class,'emergency_id');
    }
    public function owner(){
        return $this->belongsTo(User::class,'user_id');
    }

}

