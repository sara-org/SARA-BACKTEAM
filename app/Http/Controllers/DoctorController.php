<?php

namespace App\Http\Controllers;
use App\Models\Vaccination;
use Illuminate\Support\Facades\Sanctum;
use Illuminate\Support\Facades\Password;
use App\Helper\ResponseHelper;
use App\Models\User;
use App\Models\WorkingHours;
use App\Models\MedicalRecord;
use App\Models\Animal;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;
use Carbon\Carbon;
use Carbon\CarbonInterval;
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

        $doctors = Doctor::all();

        return response()->json(ResponseHelper::success($doctors, 'All Doctors Are retrieved'));
    }

    public function getDoctor($doctor_id)
    {
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
    // public function addWorkingHours(Request $request)
    // {
    //     if (Auth::user()->role != 3) {
    //         return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
    //         'start_time' => ['required', 'date_format:H:i'],
    //         'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
    //     }

    //     $doctorId = auth('sanctum')->user()->id;
    //     $data = $request->all();
    //     $data['doctor_id'] = $doctorId;
    //     $workingHours = WorkingHours::create($data);

    //     return response()->json(ResponseHelper::created($workingHours, 'Working hours for doctor added'));
    // }
    public function addWorkingHours(Request $request)
    {
        if (Auth::user()->role != 3) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }

        $validator = Validator::make($request->all(), [
            'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($validator->fails()) {
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }

        $doctorId = auth('sanctum')->user()->id;
        $data = $request->all();
        $data['doctor_id'] = $doctorId;
        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $interval = CarbonInterval::minutes(30);
        $workingHours = [];

        while ($start < $end) {
            $workingHours[] = [
                'doctor_id' => $data['doctor_id'],
                'day' => $data['day'],
                'start_time' => $start->format('H:i'),
                'end_time' => $start->addMinutes(30)->format('H:i'),
            ];
        }

        WorkingHours::insert($workingHours);

        return response()->json(ResponseHelper::created($workingHours, 'Working hours for doctor added'));
    }
    public function updateWorkingHours(Request $request, $id)
    {
        if (Auth::user()->role != 3) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }

        $validator = Validator::make($request->all(), [
            'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($validator->fails()) {
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }

        $doctorId = auth('sanctum')->user()->id;
        $workingHours = WorkingHours::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$workingHours) {
            return response()->json(ResponseHelper::error(null, 'Working hours not found', 'Not found', 404));
        }

        $workingHours->update($request->all());

        return response()->json(ResponseHelper::success($workingHours, 'Working hours updated successfully'));
    }
    public function getWorkingHours()
    {
        $doctorId = auth('sanctum')->user()->id;
        $workingHours = WorkingHours::where('doctor_id', $doctorId)->get();

        return response()->json(ResponseHelper::success($workingHours, 'Doctor working hours retrieved successfully'));
    }
    public function deleteWorkingHours($id)
    {
        $doctorId = auth('sanctum')->user()->id;
        $workingHours = WorkingHours::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (!$workingHours) {
            return response()->json(ResponseHelper::error(null, 'Working hours not found', 'Not found', 404));
        }

        $workingHours->delete();

        return response()->json(ResponseHelper::success(null, 'Working hours deleted successfully'));
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
public function addAppointment(Request $request)
{
    if (Auth::user()->role != 2) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $validator = Validator::make($request->all(), [
        'doctor_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', 3);
        })],
        'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
        'reserved_time' => ['required', 'date_format:H:i'],
    ]);

    if ($validator->fails()) {
        return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
    }

    $doctor = User::find($request->doctor_id);

    if (!$doctor) {
        return response()->json(ResponseHelper::error([], null, 'Doctor not found', 404));
    }

    $reservedTime = Carbon::parse($request->reserved_time);
    $currentTime = Carbon::parse($request->reserved_time);
    $halfHourLater = $currentTime->copy()->addMinutes(30);

    $workingHours = WorkingHours::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('start_time', '<=', $reservedTime->format('H:i'))
        ->where('end_time', '>=', $reservedTime->format('H:i'))
        ->first();

    if (!$workingHours) {
        return response()->json(ResponseHelper::error([], null, 'Invalid appointment', 422));
    }

$lastAppointmentEndTime = Appointment::where('doctor_id', $doctor->id)
    ->where('day', $request->day)
    ->where('reserved_time', '<', $currentTime->format('H:i'))
    ->orderBy('reserved_time', 'desc')
    ->value('reserved_time');

// if ($lastAppointmentEndTime) {
//     $lastAppointmentEndTime = Carbon::parse($lastAppointmentEndTime);
//     $minimumAllowedTime = $lastAppointmentEndTime->copy()->addMinutes(30);

//     if ($reservedTime < $minimumAllowedTime) {
//         return response()->json(ResponseHelper::error([], null, 'Another appointment exists within the next half hour', 422));
//     }
if ($lastAppointmentEndTime) {
    $lastAppointmentEndTime = Carbon::parse($lastAppointmentEndTime);

    if ($reservedTime <= $lastAppointmentEndTime) {
        return response()->json(ResponseHelper::error([], null, 'Another appointment exists within the same time slot', 422));
    }
}
$existingAppointment = Appointment::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('reserved_time', $request->reserved_time)
        ->first();

        $lastAppointmentEndTime = Appointment::where('doctor_id', $doctor->id)
    ->where('day', $request->day)
    ->where('reserved_time', '<', $currentTime->format('H:i'))
    ->orderBy('reserved_time', 'desc')
    ->value('reserved_time');

    if ($existingAppointment) {
        return response()->json(ResponseHelper::error([], null, 'Appointment already exists at this time', 422));
    }

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'day' => $request->day,
        'reserved_time' => $request->reserved_time,
    ]);

    return response()->json(ResponseHelper::created($appointment, 'Appointment created'));
}

public function updateAppointment(Request $request, $id)
{
    if (Auth::user()->role != 2) {
        return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
    }

    $validator = Validator::make($request->all(), [
        'doctor_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', 3);
        })],
        'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
        'reserved_time' => ['required', 'date_format:H:i'],
    ]);

    if ($validator->fails()) {
        return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
    }

    $doctor = User::find($request->doctor_id);

    if (!$doctor) {
        return response()->json(ResponseHelper::error([], null, 'Doctor not found', 404));
    }

    $reserved_time = Carbon::parse($request->reserved_time)->format('H:i');

    $workingHours = WorkingHours::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('start_time', '<=', $reserved_time)
        ->where('end_time', '>=', $reserved_time)
        ->first();

    if (!$workingHours) {
        return response()->json(ResponseHelper::error([], null, 'Invalid appointment', 422));
    }

    $existingAppointment = Appointment::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('reserved_time', $request->reserved_time)
        ->where('id', '!=', $id)
        ->first();

    if ($existingAppointment) {
        return response()->json(ResponseHelper::error([], null, 'Appointment already exists at this time', 422));
    }

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(ResponseHelper::error([], null, 'Appointment not found', 404));
    }

    $appointment->doctor_id = $doctor->id;
    $appointment->day = $request->day;
    $appointment->reserved_time = $request->reserved_time;
    $appointment->save();

    return response()->json(ResponseHelper::success($appointment, 'Appointment updated'));
}
public function getAppointmentsForDay(Request $request)
{
    $day = $request->input('day');

    $appointments = Appointment::where('day', $day)->get();

    return response()->json([
        'appointments' => $appointments,
    ]);
}
public function getAppointmentsForDoctorAndDay(Request $request)
{
    $doctorId = $request->input('doctor_id');
    $day = $request->input('day');

    $appointments = Appointment::where('doctor_id', $doctorId)
        ->where('day', $day)
        ->get();

    return response()->json([
        'appointments' => $appointments,
    ]);
}
public function getAppointmentById($id)
{
    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(ResponseHelper::error([], null, 'Appointment not found', 404));
    }

    return response()->json([
        'appointment' => $appointment,
    ]);
}
public function deleteAppointment($id)
{
    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(ResponseHelper::error([], null, 'Appointment not found', 404));
    }

    $appointment->delete();

    return response()->json([
        'message' => 'Appointment deleted successfully',
    ]);
}
}
