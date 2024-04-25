<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AnimalService
{
    public function updateAnimal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'age' => 'required',
            'photo' => 'required',
            'entry_date' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $user = Auth::user();
    
        if ($user->role !== '4') {
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
    
        $animal->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Animal updated successfully',
            'data' => $animal,
        ], 200);
    }
    public function deleteAnimal($id)
    {
        $user = Auth::user();
    
        if (Auth::user()->role !== '4' && Auth::user()->role !== '2') {
            return ResponseHelper::success([],null,'Unauthorized',401);

        }
    
        $animal =Animal::find($id);
    
        if (!$animal) {
            return ResponseHelper::success([],null,'Animal not found',200);
        }
          

                $animal->delete();
    return ResponseHelper::success([],null,'Animal deleted successfully',200);
     
        }

}