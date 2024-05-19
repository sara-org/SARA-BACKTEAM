<?php

namespace App\Http\Controllers;
use App\Models\Vaccination;
use Illuminate\Support\Facades\Sanctum;
use Illuminate\Support\Facades\Password;
use App\Helper\ResponseHelper;
use App\Models\User;
use App\Models\Employee;
use App\Models\Animal;
use App\Models\Feeding;
use App\Models\Donation;
use App\Models\Adoption;
use App\Models\Doctor;
use App\Models\Sponcership;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorController extends Controller
{
    public function addDoctor(Request $request)
    {
        if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }  
        $validator = Validator::make($request->all(), [
            'age' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 3)],
        ]);

         
        if ($validator->fails()) {
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }

        $doctor = Doctor::create($request->all());

        return response()->json(ResponseHelper::created($doctor , ' Doctor created'));
    }  

    public function updateDoctor(Request $request, $doctor_id)
    {
        if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        } 
        $validator = Validator::make($request->all(), [
            'age' => ['required', 'integer'],
            'address' => ['required', 'string'],
        ]);


        if ($validator->fails()) {
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return response()->json(ResponseHelper::error([], null, 'Doctor not found', 404));
        }

        $doctor->update($request->all());

        return response()->json(ResponseHelper::updated($doctor, 'Doctor updated'));
    }
    public function getAllDoctors()
    {
        
        // if (Auth::user()->role != 2) {
        //     return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        // }

        $doctors = Doctor::all();

        return response()->json(ResponseHelper::success($doctors, 'All Doctors Are retrieved'));
    }

    public function getDoctor($doctor_id)
    {
        
        // if (Auth::user()->role != 2) {
        //     return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        // } 
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return response()->json(ResponseHelper::error([], null, 'Doctor not found', 404));
        }

        return response()->json(ResponseHelper::success($doctor, 'Doctor retrieved'));
    }

    public function deleteDoctor($doctor_id)
    {    
       
      if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return response()->json(ResponseHelper::error([], null, 'Doctor not found', 404));
        }

        $doctor->delete();

        return response()->json(ResponseHelper::success([], 'Doctor deleted'));
    }

}
