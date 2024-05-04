<?php

namespace App\Http\Controllers;
use App\Models\AnimalType;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class AnimalTypeController extends Controller
{
    public function addAnimalType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);
    
        if ($validator->fails()) {
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
    
        $animaltype = AnimalType::create(['type' => $request->type]);
    
        return response()->json([
            'status' => true,
            'message' => 'Animal type created successfully',
            'data' => $animaltype,
        ], 201);
    }
    public function updateAnimalType(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
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
    
        $animaltype = AnimalType::find($id);
    
        if (!$animaltype) {
            return response()->json([
                'status' => false,
                'message' => 'Animal Type not found',
            ], 404);
        }
    
        $animaltype->type = $request->type;
        $animaltype->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Animal Type updated successfully',
            'data' => $animaltype,
        ], 200);
    }
   
    public function getAllAnimalsTypes()
    {
        // try {
        //     if (Auth::user()->role !== '4' && Auth::user()->role !== '2') {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Only the admin and employees can get all animals types',
        //         ], 403);
        //     }
    
            $animalstypes = AnimalType::all();
    
            return response()->json([
                'status' => true,
                'message' => 'Animals Types Retrieved Successfully',
                'data' => $animalstypes
            ], 200);
        }
        //  catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => $th->getMessage()
        //     ], 500);
        // }
    

    public function deleteAnimalType($id)
    {
        $user = Auth::user();
    
        if (Auth::user()->role !== '4' && Auth::user()->role !== '2') {
            return ResponseHelper::success([],null,'Unauthorized',401);

        }
    
        $animaltype =AnimalType::find($id);

        if (!$animaltype) {
            return ResponseHelper::success([],null,'Animal Type not found',200);
        }
                $animaltype->delete();
    return ResponseHelper::success([],null,'Animal Type deleted successfully',200);
     
    }
}


