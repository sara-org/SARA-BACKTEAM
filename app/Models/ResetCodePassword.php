<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetCodePassword extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'email',
        'code',
       ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
}

