<?php

namespace App\Http\Controllers;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Animal;
use App\Models\Feeding;
use App\Models\Adoption;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Sponcership;
use App\Models\Vaccination;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeController extends Controller
{
public function addEmployee(Request $request)
{
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        $validator = Validator::make($request->all(), [
            'age' => ['required', 'integer'],
            'job_title' => ['required', 'string'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => [
                'required',
                'date_format:H:i:s',
                Rule::notIn([$request->input('start_time')])
            ],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 4)],
        ]);


        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }
        $data=$request->all();
        $data['is_verified']=true;
        $employee = Employee::create($data);

        return ResponseHelper::created($employee, 'Employee created');
}
public function updateEmployee(Request $request, $employee_id)
{
        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        $validator = Validator::make($request->all(), [
            'age' => ['required', 'integer'],
            'job_title' => ['required', 'string'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => [
                'required',
                'date_format:H:i:s',
                Rule::notIn([$request->input('start_time')])
            ],
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
        }
        $employee = Employee::find($employee_id);

        if (!$employee) {
            return ResponseHelper::error([], null, 'Employee not found', 404);
        }

        $employee->update($request->all());

        return ResponseHelper::updated($employee, 'Employee updated');
}
public function getAllEmployees()
{

        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $employees = Employee::with('user')->where('is_verified',request('is_verified')?? true)->get();

        return ResponseHelper::success($employees, 'Employees retrieved');
    }
    public function getAllEmpReq()
    {

        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }

        $employees = Employee::with('user')->where('is_verified',request('is_verified')?? false)->get();

        return ResponseHelper::success($employees, 'Employees requests retrieved');
    }
    public function getEmployee($employee_id)
    {

        if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        $employee = Employee::with('user')->find($employee_id);

        if (!$employee) {
            return ResponseHelper::error([], null, 'Employee not found', 404);
        }

        return ResponseHelper::success($employee, 'Employee retrieved');
    }
    public function deleteEmployee($employee_id)
    {

      if (Auth::user()->role != '2') {
            return ResponseHelper::error(null, null, 'Unauthorized', 401);
        }
        $employee = Employee::find($employee_id);

        if (!$employee) {
            return ResponseHelper::error([], null, 'Employee not found', 404);
        }

        $employee->delete();

        return ResponseHelper::success([], 'Employee deleted');
}
public function reqSponcership(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'animal_id' => 'required|exists:animals,id',
            'balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = Auth::user();

        if ($user->wallet <= 0)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Wallet balance is zero or negative. Cannot request sponsorship.',
            ], 400);
        }
        $animal = Animal::findOrFail($request->input('animal_id'));
        $adoption = Adoption::where('animal_id', $animal->id)->first();

        if ($adoption && $adoption->adop_status == 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Animal is already adopted',
            ], 400);
        }

        $lastSponcership = Sponcership::where('animal_id', $animal->id)
            ->where('spon_status', 1)
            ->latest()
            ->first();

        if ($lastSponcership && $lastSponcership->user_id == $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot request sponsorship again within a month',
            ], 400);
        }

        $sponcershipData = [
            'sponcership_date' => Carbon::now(),
            'user_id' => $user->id,
            'animal_id' => $request->input('animal_id'),
            'spon_status' => 0,
            'balance' => $request->input('balance'),
        ];

        $sponcership = Sponcership::create($sponcershipData);

        return ResponseHelper::created($sponcership, 'Sponcership requested successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User or animal not found', 404);
    } catch (ValidationException $exception) {
        return ResponseHelper::error([], null, $exception->errors(), 422);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function ApproveSponcership(Request $request, $sponcershipId)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $sponcership = Sponcership::findOrFail($sponcershipId);
        $sponcership->spon_status = 1;
        $sponcership->save();

        $user = $sponcership->user;
        if ($user->wallet >= 0)
        {
            $user->wallet -= $sponcership->balance;
            $user->save();
        }
        else
        {
            return ResponseHelper::error([], null, 'Wallet balance is zero or negative', 400);
        }
        return ResponseHelper::success($sponcership, 'Sponcership approved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Sponcership not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getPendingSponcerships()
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $sponcerships = Sponcership::where('spon_status', 0)->get();

        $response = [];

        foreach ($sponcerships as $sponcership) {
            $user = User::find($sponcership->user_id);
            $animal = Animal::find($sponcership->animal_id);

            $sponcershipDetails = [
                'sponcership_id' => $sponcership->id,
                'sponcership_date' => $sponcership->sponcership_date,
                'spon_status' => $sponcership->spon_status,
                'user' => $user,
                'animal' => $animal,
                'created_at' => $sponcership->created_at,
                'updated_at' => $sponcership->updated_at,
            ];

            $response[] = $sponcershipDetails;
        }

        return ResponseHelper::success($response, 'Pending sponsorships retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAcceptingSponcerships()
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $sponcerships = Sponcership::where('spon_status', 1)->get();

        $response = [];

        foreach ($sponcerships as $sponcership) {
            $user = User::find($sponcership->user_id);
            $animal = Animal::find($sponcership->animal_id);

            $sponcershipDetails = [
                'sponcership_id' => $sponcership->id,
                'sponcership_date' => $sponcership->sponcership_date,
                'spon_status' => $sponcership->spon_status,
                'user' => $user,
                'animal' => $animal,
                'created_at' => $sponcership->created_at,
                'updated_at' => $sponcership->updated_at,
            ];

            $response[] = $sponcershipDetails;
        }

        return ResponseHelper::success($response, 'Approving sponsorships retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserSponcerships($user_id)
{
    try {
        $loggedInUser = Auth::user();
        if ( $loggedInUser->id != $user_id && $loggedInUser->role != 2) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $sponcerships = Sponcership::where('user_id', $user_id)
            ->where('spon_status', 1)
            ->with('animal')
            ->get();

        return ResponseHelper::success($sponcerships, 'User sponsorships retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteSponcership($sponcership_id)
{
    try {
        $sponcership = Sponcership::findOrFail($sponcership_id);


        if (Auth::user()->role !== '2'&& Auth::user()->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $sponcership->delete();

        return ResponseHelper::success([], 'Sponcership deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Sponcership not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function ReqAdoption(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'animal_id' => 'required|exists:animals,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $animal = Animal::findOrFail($request->input('animal_id'));
        $userId = Auth::user()->id;

        $existingAdoption = Adoption::where('animal_id', $animal->id)
            ->where('user_id', $userId)
            ->where('adop_status', 1)
            ->latest()
            ->first();


        if ($existingAdoption) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Adoption already exists for this animal',
                ],
                400
            );
        }

        $adoption = Adoption::create([
            'adoption_date' => now()->format('Y-m-d H:i:s'),
            'user_id' => $userId,
            'animal_id' => $request->input('animal_id'),
            'adop_status' => 0,
        ]);

        return ResponseHelper::created($adoption, 'Adoption requested successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Animal not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function ApproveAdoption(Request $request, $adoptionId)
{
    try {
        if (Auth::user()->role !== '2')
        {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $adoption = Adoption::findOrFail($adoptionId);
        $adoption->adop_status = 1;
        $adoption->save();

        Adoption::where('animal_id', $adoption->animal_id)
        ->where('adop_status', 0)
        ->delete();

        Sponcership::where('animal_id', $adoption->animal_id)
        ->delete();

        return ResponseHelper::success($adoption, 'Adoption approved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Adoption not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getPendingAdoptions()
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $adoptions = Adoption::where('adop_status', 0)->get();

        $response = [];

        foreach ($adoptions as $adoption) {
            $user = User::find($adoption->user_id);
            $animal = Animal::find($adoption->animal_id);

            $adoptionDetails = [
                'adoption_id' => $adoption->id,
                'adoption_date' => $adoption->adoption_date,
                'adop_status' => $adoption->adop_status,
                'user' => $user,
                'animal' => $animal,
                'created_at' => $adoption->created_at,
                'updated_at' => $adoption->updated_at,
            ];

            $response[] = $adoptionDetails;
        }

        return ResponseHelper::success($response, 'Pending adoptions retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserAdoptions($user_id)
{
    try {
         $loggedInUser = Auth::user();

            if ($loggedInUser->id != $user_id && $loggedInUser->role != 2) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }
            $adoptions = Adoption::where('user_id', $user_id)
            ->where('adop_status', 1)
            ->with('animal')
            ->get();
        return ResponseHelper::success($adoptions, 'User adoptions retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAcceptingAdoptions()
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $adoptions = Adoption::where('adop_status', 1)->get();

        $response = [];

        foreach ($adoptions as $adoption) {
            $user = User::find($adoption->user_id);
            $animal = Animal::find($adoption->animal_id);

            $adoptionDetails = [
                'adoption_id' => $adoption->id,
                'adoption_date' => $adoption->adoption_date,
                'adop_status' => $adoption->adop_status,
                'user' => $user,
                'animal' => $animal,
                'created_at' => $adoption->created_at,
                'updated_at' => $adoption->updated_at,
            ];

            $response[] = $adoptionDetails;
        }

        return ResponseHelper::success($response, 'Approving adoptions retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteAdoption($adoption_id)
{
    try {
        $adoption = Adoption::findOrFail($adoption_id);


        if (Auth::user()->role !== '2'&& Auth::user()->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $adoption->delete();

        return ResponseHelper::success([], 'Adoption deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Adoption not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function addFeeding(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = Auth::user();
        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $feedingData = [
            'department_id' => $request->input('department_id'),
            'user_id' => Auth::user()->id,
            'feeding_date' => carbon::now()->format('Y-m-d'),
        ];

        $existingFeeding = Feeding::where('department_id', $request->input('department_id'))
        ->whereDate('feeding_date', Carbon::parse($feedingData['feeding_date'])->toDateString())
        ->first();

    if ($existingFeeding) {
        return response()->json([
            'status' => 'error',
            'message' => 'Duplicate feeding for this department and date',
        ], 400);
    }


        $feeding = Feeding::create($feedingData);

        return ResponseHelper::created($feeding, 'Feeding added successfully');
    } catch (ValidationException $exception) {
        return ResponseHelper::error([], null, $exception->getMessage(), 400);
    } catch (\Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function updateFeeding(Request $request, $feedingId)
{
    try {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = Auth::user();
        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $feeding = Feeding::where('id', $feedingId)
            ->where('user_id', $user->id)
            ->first();

        if (!$feeding) {
            return ResponseHelper::error([], null, 'Feeding not found', 404);
        }

        $feeding->department_id = $request->input('department_id');
        $feeding->feeding_date = Carbon::now()->format('Y-m-d');
        $feeding->save();

        return ResponseHelper::success($feeding, 'Feeding updated successfully');
    }
    catch (ValidationException $exception) {
        return ResponseHelper::error([], null, $exception->getMessage(), 400);
    }
    catch (\Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUnfedDepartments()
{
    try {
        $user = Auth::user();
        if ($user->role !== '2' && $user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $today = Carbon::now()->toDateString();

        $unfedDepartments = Department::whereDoesntHave('feedings', function ($query) use ($today) {
            $query->whereDate('feeding_date', $today);
        })->get();

        $currentTime = Carbon::now();
        $message = 'Unfed departments retrieved successfully at ' . $currentTime;

        return ResponseHelper::success($unfedDepartments, $message);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserFeedings($user_id)
{
    try {
        $loggedInUser = Auth::user();
        if ($loggedInUser->role !== '2' && $loggedInUser->id != $user_id) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $user = User::findOrFail($user_id);
        $feedingDepartments = $user->feedings()->with('department')->whereDate('feeding_date', Carbon::today())->get();

        $departments = $feedingDepartments->map(function ($feeding) {
            return $feeding->department;
        });

        return ResponseHelper::success($departments, 'User departments for feeding retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllFeedings()
{
    $user = Auth::user();
    if ($user->role !== '2') {
        return ResponseHelper::error([], null, 'Unauthorized', 401);
    }
    $feedings = Feeding::all();

    return ResponseHelper::success($feedings, 'Feedings retrieved successfully');
}
public function deleteFeeding($feeding_id)
{
    try {
        $feeding = Feeding::findOrFail($feeding_id);

        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $feeding->delete();

        return ResponseHelper::success([], 'Feeding deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Feeding not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function addVaccination(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $vaccinationData = [
            'department_id' => $request->input('department_id'),
            'user_id' => $user->id,
            'vaccination_date' => Carbon::now()->format('Y-m-d'),
        ];

        $existingVaccination = Vaccination::where('department_id', $vaccinationData['department_id'])
        ->whereDate('vaccination_date', Carbon::parse($vaccinationData['vaccination_date'])->toDateString())
        ->first();

    if ($existingVaccination) {
        return response()->json([
            'status' => 'error',
            'message' => 'Duplicate vaccination for this department and date',
        ], 400);
    }

        $vaccination = Vaccination::create($vaccinationData);

        return ResponseHelper::created($vaccination, 'Vaccination added successfully');
    } catch (ValidationException $exception) {
        return ResponseHelper::error([], null, $exception->getMessage(), 400);
    } catch (\Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function updateVaccination(Request $request, $vaccinationId)
{
    try {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = Auth::user();

        if ($user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $vaccination = Vaccination::where('id', $vaccinationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$vaccination) {
            return ResponseHelper::error([], null, 'Vaccination not found', 404);
        }

        $vaccination->department_id = $request->input('department_id');
        $vaccination->vaccination_date = Carbon::parse($request->input('vaccination_date'));
        $vaccination->save();

        return ResponseHelper::success($vaccination, 'Vaccination updated successfully');
    } catch (ValidationException $exception) {
        return ResponseHelper::error([], null, $exception->getMessage(), 400);
    } catch (\Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserVaccinations($user_id)
{
    try {
        $loggedInUser = Auth::user();

        if ($loggedInUser->id != $user_id && $loggedInUser->role != 2) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $user = User::findOrFail($user_id);
        $vaccinationDepartments = $user->vaccinations()->with('department')->whereDate('vaccination_date', Carbon::today())->get();

        $departments = $vaccinationDepartments->map(function ($vaccination) {
            return $vaccination->department;
        });

        return ResponseHelper::success($departments, 'User departments for vaccination retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllVaccinations()
{
    $vaccinations = Vaccination::all();

    return ResponseHelper::success($vaccinations, 'Vaccinations retrieved successfully');
}
public function deleteVaccination($vaccination_id)
{
    try {

        $vaccination = Vaccination::findOrFail($vaccination_id);
        if (Auth::user()->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $vaccination->delete();

        return ResponseHelper::success([], 'Vaccination deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Vaccination not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUnVacDepartments()
{
    try {
        $user = Auth::user();
        if ($user->role !== '2' && $user->role !== '4') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $unVacDepartments = Department::whereDoesntHave('vaccinations')->get();

        return ResponseHelper::success($unVacDepartments, 'UnVac departments retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
}
