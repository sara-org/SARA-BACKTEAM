<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles'; 
protected $primaryKey = 'role_id';
 protected $fillable = ['name'];

    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}