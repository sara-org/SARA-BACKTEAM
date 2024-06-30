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

       ];
       public function userEmergencies()
    {
        return $this->hasMany(UserEmr::class);
    }

}

