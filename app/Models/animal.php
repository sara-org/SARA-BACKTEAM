<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnimalType;

class Animal extends Model
{
    use HasFactory;
    
    protected $table = 'animals';
 
    protected $fillable = ['name', 'age', 'photo', 'entry_date', 'animaltype_id', 'department_id'];

    public function animalType()
    {
        return $this->belongsTo(AnimalType::class, 'animaltype_id');
    }
}