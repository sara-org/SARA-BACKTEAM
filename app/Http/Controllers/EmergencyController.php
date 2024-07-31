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
use Carbon\Carbon;

class EmergencyController extends Controller
{
    public function addEmergency(Request $request)
    {
        try {
            if (!Auth::check()) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }
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
                'user_id' => Auth::id(),
                'emr_date' => Carbon::now()->toDateString(),
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
                'photo' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $user = auth()->user();
            $emergency = Emergency::findOrFail($emergency_id);

            if ($user->id !== $emergency->user_id) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }

            $emergencyData = [];

            if ($request->has('address')) {
                $emergencyData['address'] = $request->input('address');
            }
            if ($request->has('description')) {
                $emergencyData['description'] = $request->input('description');
            }
            if ($request->has('contact')) {
                $emergencyData['contact'] = $request->input('contact');
            }
            if ($request->has('photo')) {
                $emergencyData['photo'] = $request->input('photo');
            }
            if ($request->has('emr_date')) {
                $emergencyData['emr_date'] = Carbon::parse($request->input('emr_date'))->toDateString();
            }
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
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $emergency = Emergency::where('id', $emergency_id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        return ResponseHelper::success($emergency, 'Emergency retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}

public function getUserEmergencies($user_id)
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $emergencies = Emergency::where('user_id', $user_id)->get();

        return ResponseHelper::success($emergencies, 'User emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}

public function getAllEmergencies()
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $emergencies = Emergency::all();

        return ResponseHelper::success($emergencies, 'All emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getEmergenciesByDate(Request $request)
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'emr_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $emrDate = $request->input('emr_date');
        $emergencies = Emergency::whereDate('emr_date', $emrDate)
            ->get();

        return ResponseHelper::success($emergencies, 'Emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteEmergency($emergency_id)
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $emergency = Emergency::findOrFail($emergency_id);

        if ($emergency->user_id !== Auth::user()->id) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
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
        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'date' => 'date',
            'emergency_id' => 'required|exists:emergencies,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $date = Carbon::parse($request->input('date'));
        $status = $request->input('status');
        $emergencyId = $request->input('emergency_id');

        $userEmergency = UserEmr::firstOrCreate([
            'status' => $status,
            'emergency_id' => $emergencyId,
        ], [
            'date' => $date,
            'user_id' => $user->id,
        ]);

        if (!$userEmergency->wasRecentlyCreated) {
            return ResponseHelper::error([], null, 'User emergency already exists for this case', 400);
        }

        return ResponseHelper::created($userEmergency, 'User emergency created successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function updateUserEmergency(Request $request, $user_emergency_id)
{
    try {
        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $userEmergency = UserEmr::findOrFail($user_emergency_id);

        $userEmergency->update([
            'status' => $request->input('status'),
        ]);

        return ResponseHelper::updated($userEmergency, 'User emergency updated successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User emergency not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserEmergencyById($user_emergency_id)
{
    try {
        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
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
        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $userEmergencies = UserEmr::where('user_id', $user->id)->get();

        return ResponseHelper::success($userEmergencies, 'User emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}

public function getUserEmergenciesByDate(Request $request)
{
    try {
        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $date = $request->query('date');
        $userEmergencies = UserEmr::whereDate('date', $date)->get();

        return ResponseHelper::success($userEmergencies, 'Emergencies by this date retrieved successfully');
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
