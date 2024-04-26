<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
class AnimalType extends Model
{
    use HasFactory;
    protected $table = 'animaltypes';
    protected $fillable = ['type'];

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }
}
