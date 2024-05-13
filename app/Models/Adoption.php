<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adoption extends Model
{
    use HasFactory;
    protected $fillable = [
        'adop_status',
        'adoption_date',
        'user_id',
        'animal_id',
        
       ];
       public function user()
       {
           return $this->belongsTo(User::class);
       }
}
