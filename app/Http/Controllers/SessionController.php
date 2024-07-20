<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Models\Session;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SessionController extends Controller
{
    public function addSession(Request $request)
    {
        try {
            if (Auth::user()->role !== '2') {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'num_of_attendees' => 'required|numeric',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $sessionData = [
                'title' => $request->input('title'),
                'num_of_attendees' => $request->input('num_of_attendees'),
                'date' => $request->input('date'),
                'time' => $request->input('time'),
            ];


            $session = Session::create($sessionData);

            return ResponseHelper::created($session, 'Session added successfully');
        } catch (Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
    }

    public function updateSession(Request $request, $session_id)
    {
        try {
            if (Auth::user()->role !== '2') {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'num_of_attendees' => 'required|numeric',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $session = Session::findOrFail($session_id);

            $sessionData = [];

            if ($request->has('title')) {
                $sessionData['title'] = $request->input('title');
            }
            if ($request->has('num_of_attendees')) {
                $sessionData['num_of_attendees'] = $request->input('num_of_attendees');
            }
            if ($request->has('date')) {
                $sessionData['date'] = $request->input('date');
            }
            if ($request->has('time')) {
                $sessionData['time'] = $request->input('time');
            }
            $session->update($sessionData);

            return ResponseHelper::updated($session, 'Session updated successfully');
        } catch (ValidationException $e) {
            return ResponseHelper::error([], $e->errors(), 'Validation failed', 422);
        } catch (\Exception $e) {
            return ResponseHelper::error([], $e->getMessage(), 'An error occurred', 500);
        }
    }
public function getSessionById($session_id)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $session = session::findOrFail($session_id);
        return ResponseHelper::success($session, 'session retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'session not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllSessions()
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $sessions = Session::all();
        return ResponseHelper::success($sessions, 'All sessions retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteSession($session_id)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $session = Session::findOrFail($session_id);
        $session->delete();
        return ResponseHelper::success([], 'session deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'session not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}



}
