<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'balance',
        'user_id',
        'donation_date'
       ];
       public function user()
       {
           return $this->belongsTo(User::class);
       }
}

