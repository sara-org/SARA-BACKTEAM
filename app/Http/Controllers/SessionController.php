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

            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $sessionData = [
                'title' => $request->input('title'),
                'num_of_attendees' => $request->input('num_of_attendees'),
                'date'  => Carbon::now(),
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
                $sessionData['date'] = Carbon::now();
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


public function addUserSession(Request $request)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $validator = Validator::make($request->all(), [
            'session_date' => 'date',
            'session_id' => 'required|exists:sessions,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = auth()->user();
        $user_id = $user->id;

        $data = [
            'session_date' => Carbon::parse($request->session_date),
            'user_id' => $user_id,
            'session_id' => $request->session_id,
        ];

        $usersession = UserSession::create($data);
        return ResponseHelper::created($usersession, 'User session created successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function updateUserSession(Request $request, $user_session_id)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $validator = Validator::make($request->all(), [
            'session_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'session_id' => 'nullable|exists:sessions,id',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = auth()->user();
        $usersession = UserSession::findOrFail($user_session_id);

        if ($user->id === $usersession->user_id) {
            $usersession->update($request->all());
            return ResponseHelper::updated($usersession, 'User session updated successfully');
        } else {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User session not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getUserSessionById($user_session_id)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $usersession = UserSession::findOrFail($user_session_id);
        return ResponseHelper::success($usersession, 'User session retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User session not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllUserSessions()
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $userSessions = UserSession::all();
        return ResponseHelper::success($userSessions, 'All user sessions retrieved successfully');
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function deleteUserSession($user_session_id)
{
    try {
        $user = auth()->user();
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $usersession = UserSession::findOrFail($user_session_id);
        if ($user->id === $usersession->user_id) {
            $usersession->delete();
            return ResponseHelper::success([], 'User session deleted successfully');
        } else {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User session not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
}
