<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class DepartmentController extends Controller
{
    public function addDepartment(Request $request)
    {
        $validator = Validator::make($request->all(),
         [
            'name' => 'required' , 'string',
            'number' => 'required', 'min:1',

        ]);

        if ($validator->fails())

        {
            return response()->json(
            [
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();

        if ($user->role !== '4' && $user->role !== '2') {


            return response()->json(
            [
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $department = Department::create([
            'name' => $request->name,
            'number' => $request->number,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Deparment created successfully',
            'data' => $department,
        ], 201);
    }

    public function updateDepartment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required',
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

        if ($user->role !== '4' && $user->role !== '2')
        {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access',
            ], 401);
        }

        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Department not found',
            ], 404);
        }

        $department->name = $request->name;
        $department->number = $request->number;

        $department->save();

        return response()->json([
            'status' => true,
            'message' => 'Department updated successfully',
            'data' => $department,
        ], 200);
    }
    public function getDepartmentById($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Department not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Department retrieved successfully',
            'data' => $department,
        ], 200);
    }
    public function getAllDepartments()
    {
            $departments = Department::all();

            return response()->json([
                'status' => true,
                'message' => 'Departments Retrieved Successfully',
                'data' => $departments
            ], 200);
        }


    public function deleteDepartment($id)
    {
        {
            $user = Auth::user();

            if (Auth::user()->role !== '4' && Auth::user()->role !== '2') {
                return ResponseHelper::success([],null,'Unauthorized',401);

            }

            $department=Department::find($id);

            if (!$department) {
                return ResponseHelper::success([],null,'Department not found',200);
            }
                    $department->delete();
        return ResponseHelper::success([],null,'Department deleted successfully',200);

        }
    }
}
