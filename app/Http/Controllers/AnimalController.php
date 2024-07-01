<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Animal;
use App\Services\AnimalService;

class AnimalController extends Controller
{
        public function addAnimal(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'age' => 'required',
                'photo' => ['nullable','string'],
                'entry_date' => ['required' , 'date'],
                'health' => ['required', Rule::in(['healthy', 'unhealthy', 'under treatment'])],
                'animaltype_id' => 'required|exists:animaltypes,id',
                'department_id' => 'required|exists:departments,id'
            ]);

            if ($validator->fails())

            {
                return response()->json(
                [
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $user = Auth::user();

            if ($user->role !== '4' && $user->role !== '2') {


                return response()->json(
                [
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $existingAnimal = Animal::where('name', $request->name)
            ->Where('age', $request->age)
            ->where('health', $request->health)
            ->orWhere('photo',$request->photo)
            ->Where('entry_date', $request->entry_date)
            ->Where('animaltype_id', $request->animaltype_id)
            ->first();

        if ($existingAnimal) {
            return response()->json([
                'status' => false,
                'message' => 'Duplicate animal entry',
            ], 409);
        }
        $animal = Animal::create([
            'name' => $request->name,
            'age' => $request->age,
            'photo' => $request->photo,
            'entry_date' => $request->entry_date,
            'health' => $request->health,
            'animaltype_id' => $request->animaltype_id,
            'department_id' => $request->department_id,
        ]);
            return response()->json([
                'status' => true,
                'message' => 'Animal created successfully',
                'data' => $animal,
            ], 201);
        }

    public function updateAnimal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'age' => 'required',
            'photo' => ['nullable','string'],
            'entry_date' => 'required',
            'health' => ['required', Rule::in(['healthy', 'unhealthy', 'under treatment'])],
            'animaltype_id' => 'required|exists:animaltypes,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();

        if ($user->role !== '4' && $user->role !== '2') {
        return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $animal = Animal::find($id);

        if (!$animal) {
            return response()->json([
                'status' => false,
                'message' => 'Animal not found',
            ], 404);
        }

        $animal->name = $request->name;
        $animal->age = $request->age;
        $animal->photo = $request->photo;
        $animal->entry_date = $request->entry_date;
        $animal->health = $request->health;
        $animal->animaltype_id = $request->animaltype_id;
        $animal->department_id = $request->department_id;
        $animal->save();

        return response()->json([
            'status' => true,
            'message' => 'Animal updated successfully',
            'data' => $animal,
        ], 200);
    }


    public function getAnimal($id)
    {
        $animal = Animal::with('adoptions','sponcerships')->find($id);

        if (!$animal) {
            return response()->json([
                'status' => false,
                'message' => 'Animal not found',
            ], 404);
        }
        // $animal=$animal->toArray();
        // if(! count($animal['adoptions'])){
        //     $animal['adoptions']=null;
        // }
        // if(! count($animal['sponcerships'])){
        //     $animal['sponcerships']=null;
        // }
        return response()->json([
            'status' => true,
            'message' => 'Animal retrieved successfully',
            'data' => $animal,
        ], 200);
    }
    public function getAllAnimals()
    {
            $animals = Animal::all()->toArray();
            return ResponseHelper::success($animals,null,'Animals Retrieved Successfully',200);
    }

    public function deleteAnimal($id)
    {
       $animal=app(AnimalService::class)->deleteAnimal($id);
       return  $animal;
    }
}
