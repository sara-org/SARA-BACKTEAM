<?php

namespace App\Http\Controllers;
use App\Models\Vaccination;
use Illuminate\Support\Facades\Sanctum;
use Illuminate\Support\Facades\Password;
use App\Helper\ResponseHelper;
use App\Models\User;
use App\Models\Employee;
use App\Models\MedicalRecord;
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
    public function addMedicalRecord(Request $request)
    {
        if (Auth::user()->role != 3) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }
    
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'animal_id' => ['required', 'integer', Rule::exists('animals', 'id')],
        ]);
    
        if ($validator->fails()) {
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }
    
        $doctorId = Auth::user()->id;
    
        $data = $request->all();
        $data['doctor_id'] = $doctorId;
    
        $medicalRecord = MedicalRecord::create($data);
    
        return response()->json(ResponseHelper::created($medicalRecord, 'Medical record created'));
    }
    public function updateMedicalRecord(Request $request, $id)
{
    if (Auth::user()->role != 3) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $validator = Validator::make($request->all(), [
        'date' => ['required', 'date'],
        'description' => ['required', 'string'],
        'animal_id' => ['required', 'integer', Rule::exists('animals', 'id')],
    ]);

    if ($validator->fails()) {
        return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
    }

    $doctorId = Auth::user()->id;

    $medicalRecord = MedicalRecord::find($id);

    if (!$medicalRecord) {
        return response()->json(ResponseHelper::error(null, null, 'Medical record not found', 404));
    }

    if ($medicalRecord->doctor_id != $doctorId) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $data = $request->all();
    $data['doctor_id'] = $doctorId;

    $medicalRecord->update($data);

    return response()->json(ResponseHelper::updated($medicalRecord, 'Medical record updated'));
}

public function getAllMedicalRecords()
{
    if (Auth::user()->role != 3) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $doctorId = Auth::user()->id;

    $medicalRecords = MedicalRecord::where('doctor_id', $doctorId)->get();

    return response()->json(ResponseHelper::success($medicalRecords, 'All Medical Records retrieved'));
}

public function getMedicalRecord($id)
{
    if (Auth::user()->role != 3) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $doctorId = Auth::user()->id;

    $medicalRecord = MedicalRecord::where('doctor_id', $doctorId)->find($id);

    if (!$medicalRecord) {
        return response()->json(ResponseHelper::error([], null, 'Medical record not found', 404));
    }

    return response()->json(ResponseHelper::success($medicalRecord, 'Medical record retrieved'));
}
public function deleteMedicalRecord($id)
{
    if (Auth::user()->role != 3) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $doctorId = Auth::user()->id;

    $medicalRecord = MedicalRecord::where('doctor_id', $doctorId)->find($id);

    if (!$medicalRecord) {
        return response()->json(ResponseHelper::error([], null, 'Medical record not found', 404));
    }

    $medicalRecord->delete();

    return response()->json(ResponseHelper::success([], 'Medical record deleted'));
}
}