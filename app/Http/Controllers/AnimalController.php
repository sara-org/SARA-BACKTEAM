<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
    
        $animal = Animal::create([
            'name' => $request->name,
            'age' => $request->age,
            'photo' => $request->photo,
            'entry_date' => $request->entry_date, 
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
            'photo' => 'required',
            'entry_date' => 'required',
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
        $animal->animaltype_id = $request->animaltype_id;
        $animal->department_id = $request->department_id;
        $animal->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Animal updated successfully',
            'data' => $animal,
        ], 200);
    }
   
    public function getAllAnimals()
    {
        // try {
        //     if (Auth::user()->role !== '4' && Auth::user()->role !== '2') {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Only the admin and employees can get all animals',
        //         ], 403);
        //     }
    
            $animals = Animal::all();
    
            return response()->json([
                'status' => true,
                'message' => 'Animals Retrieved Successfully',
                'data' => $animals
            ], 200);
        }
        //  catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => $th->getMessage()
        //     ], 500);
        // }
    
    public function deleteAnimal($id)
    {
       $animal=app(AnimalService::class)->deleteAnimal($id);
       return  $animal;
    }
}
