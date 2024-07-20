<?php

namespace App\Http\Controllers;
use App\Helper\ResponseHelper;
use App\Models\DocReq;
use App\Models\EmpReq;
use App\Models\Req;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequestController extends Controller
{
    public function addRequest(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        // ]);
        // if ($validator->fails()) {
        //     return ResponseHelper::error([], null, 'Validation error', 400);
        // }

        if (!auth()->check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $requestData = [
            'date' => Carbon::now(),
            'user_id' => auth('sanctum')->user()->id,
        ];
        $newRequest = Req::create($requestData);

        if ($newRequest) {
            return ResponseHelper::success($newRequest, null, 'Request added successfully', 200);
        } else {
            return ResponseHelper::error([], null, 'Server error', 500);
        }
    }
      public function updateRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // 'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }

        $requestData = [
            'date' => Carbon::now(),
            'user_id' => auth('sanctum')->user()->id,
        ];

        $updatedRequest = Req::where('id', $id)
            ->update($requestData);

        if ($updatedRequest) {
            return ResponseHelper::success([],null, 'Request updated successfully',200);
        } else {
            return ResponseHelper::error([], 'Failed to update request', 'Server error', 500);
        }
    }

    public function getRequest($id)
    {
        $request = Req::find($id);

        if ($request) {
            return ResponseHelper::success($request->toArray(), 'Request retrieved successfully');
        } else {
            return ResponseHelper::error([], 'Request not found', 'Not found', 404);
        }
    }

    public function getAllRequests()
    {
        $requests = Req::all();

        if ($requests) {
            return ResponseHelper::success($requests->toArray(), 'Requests retrieved successfully');
        } else {
            return ResponseHelper::success([], 'No requests found');
        }
    }

    public function deleteRequest($id)
    {
        $deletedRequest = Req::destroy($id);

        if ($deletedRequest) {
            return ResponseHelper::success([], null, 'Request deleted successfully',200);
        } else {
            return ResponseHelper::error([], 'Failed to delete request', 'Server error', 500);
        }
    }
    public function addEmpreq(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => ['required', 'integer', Rule::exists('requests', 'id')],
            'amount' => ['required', 'numeric'],
            'item' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return ResponseHelper::error( 'Validation error');
        }
        $user = Auth::user();
        if ($user->role != 2) {
            return ResponseHelper::error( 'Unauthorized');
        }
        $empreqData = [
            'request_id' => $request->request_id,
            'amount' => $request->amount,
            'item' => $request->item,
        ];
        $newEmpreq = EmpReq::create($empreqData);
        if ($newEmpreq) {
            return ResponseHelper::success($newEmpreq->toArray(), null,'Emp request added successfully',200);
        }
        else
        {
            return ResponseHelper::error('Failed to add Emp request');
        }
    }
    public function updateEmpreq(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => ['required', 'integer', Rule::exists('requests', 'id')],
            'amount' => ['required', 'numeric'],
            'item' => ['required', 'string', 'max:255'],
            'status' => 'string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }
        $user = Auth::user();

        if ($user->role != '4') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        $empreqData = [
            'request_id' => $request->request_id,
            'amount' => $request->amount,
            'item' => $request->item,
        ];

        $updatedEmpreq = EmpReq::where('id', $id)
            ->update($empreqData);

        if ($updatedEmpreq) {
            return ResponseHelper::success([], 'Empreq updated successfully');
        } else {
            return ResponseHelper::error([], 'Failed to update empreq', 'Server error', 500);
        }
    }
    public function updateEmpReqStatus(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role != '2') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        $empreq = EmpReq::findOrFail($id);
        $empreq->status = $request->input('status');
        $empreq->save();
        return ResponseHelper::success('Employee Request Status has updated successfully');
    }

    public function getEmpreq($id)
    {
        $empreq = EmpReq::find($id);

        if ($empreq) {
            return ResponseHelper::success($empreq->toArray(), 'Empreq retrieved successfully');
        } else {
            return ResponseHelper::error([], 'Empreq not found', 'Not found', 404);
        }
    }

    public function getAllEmpreqs()
    {
        $empreqs = EmpReq::all();

        if ($empreqs) {
            return ResponseHelper::success($empreqs->toArray(), 'Empreqs retrieved successfully');
        } else {
            return ResponseHelper::success([], 'No empreqs found');
        }
    }

    public function deleteEmpreq($id)
    {
        $deletedEmpreq = EmpReq::destroy($id);
        $user = Auth::user();

        if ($user->role != '4') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        if ($deletedEmpreq) {
            return ResponseHelper::success([], 'Empreq deleted successfully');
        } else {
            return ResponseHelper::error([], 'Failed to delete empreq', 'Server error', 500);
        }
    }

    public function addDocreq(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => ['required', 'integer', Rule::exists('requests', 'id')],
            'amount' => ['required', 'numeric'],
            'medicine' => ['required', 'string', 'max:255'],
            'status' => 'string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }
        $user = Auth::user();

        if ($user->role != '4') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        $docreqData = [
            'request_id' => $request->request_id,
            'amount' => $request->amount,
            'medicine' => $request->medicine,
        ];

        $newDocreq = DocReq::create($docreqData);

        if ($newDocreq) {
            return ResponseHelper::created($newDocreq->toArray(), 'Doctor request added successfully');
        } else {
            return ResponseHelper::error([], 'Failed to add Doctor request', 'Server error', 500);
        }
    }
    public function updateDocreq(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
           'request_id' => ['required', 'integer', Rule::exists('requests', 'id')],
            'amount' => ['required', 'numeric'],
            'medicine' => ['required', 'string', 'max:255'],
            'status' => 'string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([], $validator->errors(), 'Validation error', 400);
        }
        $user = Auth::user();

        if ($user->role != '4') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        $docreqData = [
            'request_id' => $request->request_id,
            'amount' => $request->amount,
            'medicine' => $request->medicine,
        ];

        $updatedDocreq = DocReq::where('id', $id)
            ->update($docreqData);

        if ($updatedDocreq) {
            return ResponseHelper::success([], 'Doctor request updated successfully');
        } else {
            return ResponseHelper::error([], 'Failed to update doctor request', 'Server error', 500);
        }
    }

    public function getDocreq($id)
    {
        $docreq = DocReq::find($id);

        if ($docreq) {
            return ResponseHelper::success($docreq->toArray(), 'Doctor request retrieved successfully');
        } else {
            return ResponseHelper::error([], 'Doctor request not found', 'Not found', 404);
        }
    }
    public function updateDocReqStatus(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role != '2') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        $docreq = DocReq::findOrFail($id);
        $docreq->status = $request->input('status');
        $docreq->save();
        return ResponseHelper::success('Doctor Request Status has updated successfully');
    }
    public function getAllDocreqs()
    {
        $docreqs = DocReq::all();

        if ($docreqs) {
            return ResponseHelper::success($docreqs->toArray(), 'Doctor requests retrieved successfully');
        } else {
            return ResponseHelper::success([], 'No Doctor requests found');
        }
    }
    public function deleteDocreq($id)
    {
        $deletedDocreq = DocReq::destroy($id);
        $user = Auth::user();

        if ($user->role != '4') {
            return ResponseHelper::error([], 'Unauthorized', 'Unauthorized', 401);
        }
        if ($deletedDocreq) {
            return ResponseHelper::success([], 'Doctor request deleted successfully');
        } else {
            return ResponseHelper::error([], 'Failed to delete Doctor request', 'Server error', 500);
        }
    }
}
