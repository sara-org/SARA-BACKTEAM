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
        if (Auth::user()->role != '2') {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }

        $validator = Validator::make($request->all(), [
            'age' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 3)],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }

        $existingDoctor = Doctor::where('user_id', $request->input('user_id'))->first();

        if ($existingDoctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Duplicate entry for this doctor',
            ], 400);
        }

        $doctor = Doctor::create($request->all());

        return ResponseHelper::created($doctor, 'Doctor created');
    }
    public function updateDoctor(Request $request, $doctor_id)
    {
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        $validator = Validator::make($request->all(), [
            'age' => ['required', 'integer'],
            'address' => ['required', 'string'],
        ]);


        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return ResponseHelper::error([], null, 'Doctor not found', 404);
        }

        $doctor->update($request->all());

        return ResponseHelper::updated($doctor, 'Doctor updated');
    }
    public function getAllDoctors()
    {
        $doctors = Doctor::with('user')->get();

        return ResponseHelper::success($doctors, 'All Doctors Are retrieved');
    }

    public function getDoctor($doctor_id)
    {
        $doctor = Doctor::with('user')->find($doctor_id);

        if (!$doctor) {
            return ResponseHelper::error([], null, 'Doctor not found', 404);
        }

        return ResponseHelper::success($doctor, 'Doctor retrieved');
    }
    public function deleteDoctor($doctor_id)
    {

      if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        $doctor = Doctor::with('user')->find($doctor_id);

        if (!$doctor) {
            return ResponseHelper::error([], null, 'Doctor not found', 404);
        }

        $doctor->delete();

        return ResponseHelper::success([], 'Doctor deleted');
    }
      public function addWorkingHours(Request $request)
    {
        if (Auth::user()->role != '3') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }

        $existingWorkingHours = WorkingHours::where('doctor_id', $request->input('doctor_id'))
            ->orwhere('day', $request->input('day'))
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<=', $request->input('start_time'))
                        ->where('end_time', '>=', $request->input('start_time'));
                })
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '<=', $request->input('end_time'))
                        ->where('end_time', '>=', $request->input('end_time'));
                });
            })
            ->first();

        if ($existingWorkingHours) {
            return response()->json([
                'error' => [
                    'status' => 'error',
                    'message' => 'Duplicate entry for this doctor hours',
                ]
            ], 400);
        }

        $doctorId = auth('sanctum')->user()->id;
        $data = $request->all();
        $data['doctor_id'] = $doctorId;
        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $interval = CarbonInterval::minutes(30);
        $workingHoursData = [];
        while ($start < $end) {
            $workingHoursData[] = [
                'doctor_id' => $data['doctor_id'],
                'day' => $data['day'],
                'start_time' => $start->format('H:i'),
                'end_time' => $start->addMinutes(30)->format('H:i'),
                'status' => 0,
            ];
        }
        WorkingHours::insert($workingHoursData);
        $response = [
            'data' => [
                'day' => $data['day'],
                'working_hours' => $workingHoursData
            ],
            'success' => true,
            'message' => 'Working hours for doctor added',
        ];

        return response()->json($response);
    }

    public function updateWorkingHours(Request $request)
    {
        if (Auth::user()->role != '3') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'day' => ['required', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }

        $doctorId = auth('sanctum')->user()->id;
        $day = $request->input('day');

        WorkingHours::where('doctor_id', $doctorId)
            ->where('day', $day)
            ->delete();

        $start = Carbon::parse($request->input('start_time'));
        $end = Carbon::parse($request->input('end_time'));
        $interval = CarbonInterval::minutes(30);
        $workingHoursData = [];
        while ($start < $end) {
            $workingHoursData[] = [
                'doctor_id' => $doctorId,
                'day' => $day,
                'start_time' => $start->format('H:i'),
                'end_time' => $start->addMinutes(30)->format('H:i'),
                'status' => 0,
            ];
        }
        WorkingHours::insert($workingHoursData);

        return ResponseHelper::success(['day' => $day, 'working_hours' => $workingHoursData], 'Working hours updated successfully');
    }


    public function getWorkingHours(Request $request, $doctorId)
{
    $user = Auth::user();
    if ($user->role !== '2' && $user->id != $doctorId) {
    return ResponseHelper::error(null, null, 'Unauthorized', 401);}
    $doctor = User::where('id', $doctorId)->first();
    return ResponseHelper::success($doctor->doctimes->toArray());}


    public function getNotReservedHours(Request $request, $doctorId)
    {
        $user = Auth::user();

        if ($user->role !== '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $doctor = User::find($doctorId);

        if (!$doctor) {
            return ResponseHelper::error(null, null, 'Doctor not found', 404);
        }

        $freeSlots = $doctor->doctimes()->where('status', 0)->get();

        return ResponseHelper::success($freeSlots->toArray());
    }
    public function getReservedHours(Request $request, $doctorId)
    {
        $user = Auth::user();

        if ($user->role !== '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $doctor = User::find($doctorId);

        if (!$doctor) {
            return ResponseHelper::error(null, null, 'Doctor not found', 404);
        }

        $reservedSlots = $doctor->doctimes()->where('status', 1)->get();

        return ResponseHelper::success($reservedSlots->toArray());
    }
public function getWorkingDays(Request $request, $doctorId)
{
    $user = Auth::user();

    if ($user->role !== '2' && $user->id != $doctorId) {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $doctor = User::where('id', $doctorId)->first();
    $workingDays = $doctor->doctimes->pluck('day')->toArray();
    $uniqueWorkingDays = array_unique($workingDays);
    $valuesOnly = array_values($uniqueWorkingDays);
    return ResponseHelper::success($valuesOnly);
}

public function deleteWorkingHours(Request $request)
{
    $validator = Validator::make($request->all(), [
        'doctor_id' => ['required', 'integer'],
        'day' => ['required', 'string'],
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
    }

    $doctorId = $request->input('doctor_id');
    $day = $request->input('day');
    if (Auth::user()->role !== '2' && Auth::user()->id != $request->input('doctor_id'))
    { return ResponseHelper::error(null, null, 'Unauthorized', 401);}
    WorkingHours::where('doctor_id', $doctorId)
        ->where('day', $day)
        ->delete();

    return ResponseHelper::success(['doctor_id' => $doctorId, 'day' => $day], 'Working hours deleted successfully');
}

    public function addMedicalRecord(Request $request)
    {
        if (Auth::user()->role != '3') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'animal_id' => ['required', 'integer', Rule::exists('animals', 'id')],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }

        $doctorId = Auth::user()->id;

        $data = $request->all();
        $data['doctor_id'] = $doctorId;

        $medicalRecord = MedicalRecord::create($data);

        return ResponseHelper::created($medicalRecord, 'Medical record created');
    }
    public function updateMedicalRecord(Request $request, $id)
{
    if (Auth::user()->role != '3') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $validator = Validator::make($request->all(), [
        'date' => ['required', 'date'],
        'description' => ['required', 'string'],
        'animal_id' => ['required', 'integer', Rule::exists('animals', 'id')],
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
    }

    $doctorId = Auth::user()->id;

    $medicalRecord = MedicalRecord::find($id);

    if (!$medicalRecord) {
        return ResponseHelper::error(null, null, 'Medical record not found', 404);
    }

    if ($medicalRecord->doctor_id != $doctorId) {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $data = $request->all();
    $data['doctor_id'] = $doctorId;

    $medicalRecord->update($data);

    return ResponseHelper::updated($medicalRecord, 'Medical record updated');
}

public function getAllMedicalRecords()
{
    if (Auth::user()->role != '3') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $doctorId = Auth::user()->id;

    $medicalRecords = MedicalRecord::where('doctor_id', $doctorId)->get();

    return ResponseHelper::success($medicalRecords, 'All Medical Records retrieved');
}
public function getMedicalRecordsForAnimal(Request $request, $animalId)
{
    if (Auth::user()->role != '3') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }
    $validator = Validator::make(['animal_id' => $animalId], [
        'animal_id' => ['required', 'integer', Rule::exists('animals', 'id')],
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
    }

    $medicalRecords = MedicalRecord::where('animal_id', $animalId)->get();

    return ResponseHelper::success($medicalRecords, 'Medical records retrieved successfully');
}
public function getMedicalRecord($id)
{
    if (Auth::user()->role != '3') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $doctorId = Auth::user()->id;

    $medicalRecord = MedicalRecord::where('doctor_id', $doctorId)->find($id);

    if (!$medicalRecord) {
        return ResponseHelper::error([], null, 'Medical record not found', 404);
    }

    return ResponseHelper::success($medicalRecord, 'Medical record retrieved');
}
public function deleteMedicalRecord($id)
{
    if (Auth::user()->role != '3') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $doctorId = Auth::user()->id;

    $medicalRecord = MedicalRecord::where('doctor_id', $doctorId)->find($id);

    if (!$medicalRecord) {
        return ResponseHelper::error([], null, 'Medical record not found', 404);
    }

    $medicalRecord->delete();

    return ResponseHelper::success([], 'Medical record deleted');
}


public function addAppointment(Request $request)
{
    if (Auth::user()->role != '2') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $validator = Validator::make($request->all(), [
        'doctor_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', 3);
        })],
        'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
        'reserved_time' => ['required', 'date_format:H:i'],
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
    }

    $doctor = User::find($request->doctor_id);

    if (!$doctor) {
        return ResponseHelper::error([], null, 'Doctor not found', 404);
    }

    $reservedTime = Carbon::parse($request->reserved_time);
    $currentTime = Carbon::now();
    $halfHourLater = $currentTime->copy()->addMinutes(30);

        $workingHours = WorkingHours::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('start_time', $request->reserved_time)
        ->where('end_time', '>', $request->reserved_time)
        ->first();
        if ($workingHours) {
            $workingHours->status = 1;
            $workingHours->save();
        }
    if (!$workingHours) {
        return ResponseHelper::error([], null, 'Invalid appointment roles', 422);
    }

    $date = Carbon::now()->format('Y-m-d');

    $existingAppointment = Appointment::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('reserved_time', $request->reserved_time)
        ->where('date', '<=', $reservedTime->addDays(7)->format('Y-m-d'))
        ->where('date', '>=', $date)
        ->first();

    if ($existingAppointment) {
        return ResponseHelper::error([], null, 'Appointment already exists at this time', 422);
    }

    $latestAppointments = Appointment::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('reserved_time', $request->reserved_time)
        ->where('date', '>=', $currentTime->subDays(7)->format('Y-m-d'))
        ->get();

    if ($latestAppointments->isNotEmpty()) {
        return ResponseHelper::error([], null, 'Another appointment exists within the same time slot in the last 7 days', 422);
    }

    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'day' => $request->day,
        'reserved_time' => $request->reserved_time,
        'date' => $date,
    ]);

    return ResponseHelper::created($appointment, 'Appointment created');
}
public function updateAppointment(Request $request, $id)
{
    if (Auth::user()->role != '2') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $validator = Validator::make($request->all(), [
        'doctor_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', 3);
        })],
        'day' => ['required', 'string', Rule::in(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])],
        'reserved_time' => ['required', 'date_format:H:i'],
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
    }

    $doctor = User::find($request->doctor_id);

    if (!$doctor) {
        return ResponseHelper::error([], null, 'Doctor not found', 404);
    }

    $reserved_time = Carbon::parse($request->reserved_time)->format('H:i');

    $workingHours = WorkingHours::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('start_time', '<=', $reserved_time)
        ->where('end_time', '>=', $reserved_time)
        ->first();

    if (!$workingHours) {
        return ResponseHelper::error([], null, 'Invalid appointment', 422);
    }

    $existingAppointment = Appointment::where('doctor_id', $doctor->id)
        ->where('day', $request->day)
        ->where('reserved_time', $request->reserved_time)
        ->where('id', '!=', $id)
        ->first();

    if ($existingAppointment) {
        return ResponseHelper::error([], null, 'Appointment already exists at this time', 422);
    }

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return ResponseHelper::error([], null, 'Appointment not found', 404);
    }

    $appointment->doctor_id = $doctor->id;
    $appointment->day = $request->day;
    $appointment->reserved_time = $request->reserved_time;
    $appointment->save();

    return ResponseHelper::success($appointment, 'Appointment updated');
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
        return ResponseHelper::error([], null, 'Appointment not found', 404);
    }

    return response()->json([
        'appointment' => $appointment,
    ]);
}

public function getReservedAppointments(Request $request)
{
    if (Auth::user()->role != '2') {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }

    $date = Carbon::parse($request->date);

    $startOfDay = $date->copy()->startOfDay();
    $endOfDay = $date->copy()->endOfDay();

    $reservedAppointments = Appointment::where('date', '>=', $startOfDay)
        ->where('date', '<=', $endOfDay->addDays(7))
        ->get();

    return ResponseHelper::success($reservedAppointments, 'Reserved appointments for today and the next week');
}

public function deleteAppointment($id)
{
    $appointment = Appointment::find($id);

    if (!$appointment) {
        return ResponseHelper::error([], null, 'Appointment not found', 404);
    }

    $appointment->delete();

    return response()->json([
        'message' => 'Appointment deleted successfully',
    ]);
}
}
