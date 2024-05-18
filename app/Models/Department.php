<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'number'];

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }
    public function feedings()
    {
        return $this->hasMany(Feeding::class);
    }
 
}

