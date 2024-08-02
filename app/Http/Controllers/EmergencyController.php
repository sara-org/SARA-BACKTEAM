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
    public function reqEmergency(Request $request)
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
                'status' => 0,
            ];

            $emergency = Emergency::create($emergencyData);

            return ResponseHelper::created($emergency, 'Emergency request added successfully');
        } catch (Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
    }
    public function acceptEmergency($emergencyId)
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $user = Auth::user();
        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $emergency = Emergency::find($emergencyId);

        if (!$emergency) {
            return ResponseHelper::error([], null, 'Emergency not found', 404);
        }
        $emergency->status = 1;
        $emergency->save();

        return ResponseHelper::success($emergency, 'Emergency accepted successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function updateEmergency(Request $request, $emergencyId)
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $emergency = Emergency::find($emergencyId);

        if (!$emergency) {
            return ResponseHelper::error([], null, 'Emergency not found', 404);
        }
        if ($emergency->status !== 1) {
            return ResponseHelper::error([], null, 'Unauthorized: Emergency status is not valid', 403);
        }
        if ($emergency->user_id !== Auth::id() && Auth::user()->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized to update this emergency', 403);
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

        $emergency->address = $request->input('address');
        $emergency->description = $request->input('description');
        $emergency->contact = $request->input('contact');
        $emergency->photo = $request->input('photo');
        $emergency->save();

        return ResponseHelper::success($emergency, 'Emergency updated successfully');
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
        $loggedInUser = Auth::user();
        if ( $loggedInUser->id != $user_id && $loggedInUser->role !=  '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $emergencies = Emergency::where('user_id', $user_id)->get();

        return ResponseHelper::success($emergencies, 'User emergencies retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}

public function getAllEmergencies(Request $request)
{
    $status = $request->input('status');

    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        if(Auth::user()->role !== '2'&& Auth::user()->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $emergencies = Emergency::where('status', $status)->get();

        return ResponseHelper::success($emergencies, 'Emergencies with status '.$status.' retrieved successfully');
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
        $user = Auth::user();
        if ($user->role !== '4') {
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
        if ($emergency->status !== 1) {
            return ResponseHelper::error([], null, 'Unauthorized: Emergency status is not valid', 403);
        }
        if(Auth::user()->role !== '2'&& Auth::user()->role !== '4')
        {
            return ResponseHelper::error([], null, 'Unauthorized to delete this emergency', 403);
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
            'animal_status' => 'required|string',
            'date' => 'date',
            'emergency_id' => 'required|exists:emergencies,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $date = Carbon::parse($request->input('date'));
        $animal_status = $request->input('animal_status');
        $emergencyId = $request->input('emergency_id');

        $userEmergency = UserEmr::firstOrCreate([
            'animal_status' => $animal_status,
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
            'animal_status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $userEmergency = UserEmr::findOrFail($user_emergency_id);

        $userEmergency->update([
            'animal_status' => $request->input('animal_status'),
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
        $userEmergency = UserEmr::with('emergency','user')->findOrFail($user_emergency_id);
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
