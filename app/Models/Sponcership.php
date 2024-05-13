<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponcership extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'balance',
        'sponcership_date',
        'user_id',
        'animal_id',
        
       ];
       public function user()
       {
           return $this->belongsTo(User::class);
       }
}

