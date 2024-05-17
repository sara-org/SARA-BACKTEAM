<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\ResponseHelper;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function addEmployee(Request $request)
    {
        if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
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
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }

        $employee = Employee::create($request->all());

        return response()->json(ResponseHelper::created($employee, 'Employee created'));
    }

    public function updateEmployee(Request $request, $employee_id)
    {
        if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
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
           // 'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 4)],
        ]);


        if ($validator->fails()) {
            return response()->json(ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422));
        }
        $employee = Employee::find($employee_id);

        if (!$employee) {
            return response()->json(ResponseHelper::error([], null, 'Employee not found', 404));
        }

        $employee->update($request->all());

        return response()->json(ResponseHelper::updated($employee, 'Employee updated'));
    }
    public function getAllEmployees()
    {
        
        if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }

        $employees = Employee::all();

        return response()->json(ResponseHelper::success($employees, 'Employees retrieved'));
    }

    public function getEmployee($employee_id)
    {
        
        if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        } 
        $employee = Employee::find($employee_id);

        if (!$employee) {
            return response()->json(ResponseHelper::error([], null, 'Employee not found', 404));
        }

        return response()->json(ResponseHelper::success($employee, 'Employee retrieved'));
    }

    public function deleteEmployee($employee_id)
    {    
       
      if (Auth::user()->role != 2) {
            return response()->json(ResponseHelper::error(null, null, 'Unauthorized', 401));
        }
        $employee = Employee::find($employee_id);

        if (!$employee) {
            return response()->json(ResponseHelper::error([], null, 'Employee not found', 404));
        }

        $employee->delete();

        return response()->json(ResponseHelper::success([], 'Employee deleted'));
    }
}
