<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnimalType;

class Animal extends Model
{
    use HasFactory;
    
    protected $table = 'animals';
 
    protected $fillable = ['name', 'age', 'photo', 'entry_date','health','animaltype_id', 'department_id'];

    public function animalType()
    {
        return $this->belongsTo(AnimalType::class, 'animaltype_id');
    }
    public function adoptions()
    {
        return $this->hasMany(Adoption::class)->where('adop_status','1');
    }
    public function sponcerships()
{
    return $this->hasMany(Sponcership::class)->where('spon_status','1');
}
}