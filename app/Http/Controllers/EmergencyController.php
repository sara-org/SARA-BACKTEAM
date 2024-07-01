<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Emergency;
use App\Models\UserEmr;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmergencyController extends Controller
{
    public function addEmergency(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required|string',
                'description' => 'required|string',
                'contact' => 'required|string',
                'photo' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $emergencyData = [
                'address' => $request->input('address'),
                'description' => $request->input('description'),
                'contact' => $request->input('contact'),
                'photo' => $request->input('photo'),
            ];


            $emergency = Emergency::create($emergencyData);

            return ResponseHelper::created($emergency, 'Emergency added successfully');
        } catch (Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
    }

    public function updateEmergency(Request $request, $emergency_id)
{
    try {
        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'contact' => 'nullable|string',
            'photo' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $emergency = Emergency::findOrFail($emergency_id);

        $emergencyData = [];

        if ($request->has('address')) {$emergencyData['address'] = $request->input('address');}
        if ($request->has('description')) {$emergencyData['description'] = $request->input('description');}
        if ($request->has('contact')) {$emergencyData['contact'] = $request->input('contact');}
        if ($request->has('photo')) { $emergencyData['photo'] = $request->input('photo');}

        $emergency->update($emergencyData);

        return ResponseHelper::success($emergency, 'Emergency updated successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getEmergencyById($emergency_id)
{
    try {
        $emergency = Emergency::findOrFail($emergency_id);
        return ResponseHelper::success($emergency, 'Emergency retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllEmergencies()
{
    try {
        $emergencies = Emergency::all();
        return ResponseHelper::success($emergencies, 'All emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteEmergency($emergency_id)
{
    try {
        $emergency = Emergency::findOrFail($emergency_id);
        $emergency->delete();
        return ResponseHelper::success([], 'Emergency deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}


public function addUserEmergency(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'date' => 'required|date',
           // 'user_id' => 'required|exists:users,id',
            'emergency_id' => 'required|exists:emergencies,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
        $user = Auth::user();

        if ($user->id === $request->user_id) {
            $userEmergency = UserEmr::create($request->all());
            return ResponseHelper::created($userEmergency, 'User emergency created successfully');
        } else {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function updateUserEmergency(Request $request, $user_emergency_id)
{
    try {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|string',
            'date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'emergency_id' => 'nullable|exists:emergencies,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = Auth::user();
        $userEmergency = UserEmr::findOrFail($user_emergency_id);
        if ($user->role === '4' ) {
            $userEmergency->update($request->all());
            return ResponseHelper::updated($userEmergency, 'User emergency updated successfully');
        } else {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserEmergencyById($user_emergency_id)
{
    try {
        $userEmergency = UserEmr::findOrFail($user_emergency_id);
        return ResponseHelper::success($userEmergency, 'User emergency retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllUserEmergencies()
{
    try {
        $userEmergencies = UserEmr::all();
        return ResponseHelper::success($userEmergencies, 'All user emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteUserEmergency($user_emergency_id)
{
    try {
        $user = Auth::user();
        $userEmergency = UserEmr::findOrFail($user_emergency_id);
        if ($user->id === $userEmergency->user_id) {
            $userEmergency->delete();
            return ResponseHelper::success([], 'User emergency deleted successfully');
        } else {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
}
