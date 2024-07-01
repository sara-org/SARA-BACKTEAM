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
    protected $appends =['type','department'];


    public function getTypeAttribute(){

       $animal = Animal::where('id',$this->id)->first();
        $type= AnimalType::findOrFail($animal->animaltype_id);
       if($type){
        return $type->type;
       }
    }
    public function getDepartmentAttribute(){

        $animal = Animal::where('id',$this->id)->first();
         $department= Department::findOrFail($animal->department_id);
        if($department){
         return $department->name;
        }
     }
    public function animalType()
    {
        return $this->belongsTo(AnimalType::class, 'animaltype_id');
    }
    public function departments()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function adoptions()
    {
        return $this->hasMany(Adoption::class)->where('adop_status',  '1');
    }

    public function sponcerships()
    {
        return $this->hasMany(Sponcership::class)->whereIn('spon_status', ['0', '1']);
    }
public function medicalRecords()
{
    return $this->hasMany(MedicalRecord::class);
}
}
