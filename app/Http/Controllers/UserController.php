<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Sanctum;
use Illuminate\Support\Facades\Password;
use App\Helper\ResponseHelper;
use App\Mail\JobApplicationApprovedEmail;
use App\Models\User;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Mail\SendCodeResetPassword;
use App\Mail\VerifyAccount;
use Illuminate\Validation\ValidationException;
use Throwable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\ResetCodePassword;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
public function signUp(Request $request)
{
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', Rule::unique('users')],
            'email' => ['required', 'email', Rule::unique('users')],
            'password' => ['required', 'min:8'],
            'c_password' => ['required', 'same:password'],
            'phone' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'photo' => ['nullable' , 'string'],
            'address' => ['required', 'string'],
            'wallet' => ['numeric'],
        ]);

        if ($validator->fails())
        {
            return response()->json($validator->errors()->all(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $requestData = $request->all();
        $requestData['password'] = Hash::make($request->password);

        $user = User::create($requestData);

        $tokenResult = $user->createToken('personal Access Token')->plainTextToken;

        $data["user"] = $user;
        $data["tokenType"] = 'Bearer';
        $data["access_token"] = $user->createToken("API TOKEN")->plainTextToken;

        return response()->json($data, Response::HTTP_CREATED);
}
public function login(Request $request)
{
        $validator = Validator::make($request->all(), [
            'name' => ['required_if:email,null', 'string', Rule::exists('users')],
            'email' => ['email', Rule::exists('users')],
            'password' => ['required', 'min:8'],
        ]);

        if ($validator->fails())
        {
            return response()->json($validator->errors()->all(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = $request->only(['name', 'email', 'password']);

        if ($request->has('email')) {
            if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                $user = $request->user();
                $tokenResult = $user->createToken('personal Access Token')->plainTextToken;
                $data['user'] = $user;
                $data["TokenType"] = 'Bearer';
                $data['Token'] = $tokenResult;
            } else {
                return ResponseHelper::error([], null, 'Invalid credentials', 422);
            }
        } else {
            if (Auth::attempt(['name' => $credentials['name'], 'password' => $credentials['password']])) {
                $user = $request->user();
                $tokenResult = $user->createToken('personal Access Token')->plainTextToken;
                $data['user'] = $user;
                $data["TokenType"] = 'Bearer';
                $data['Token'] = $tokenResult;
            } else {
                return ResponseHelper::error([], null, 'Invalid credentials', 422);
            }
        }

        return response()->json($data, Response::HTTP_OK);
}
public function logout()
{
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }

        return response()->json("logged out successfully!", Response::HTTP_OK);
}
public function updateUser(Request $request, $user_id)
{
    if (Auth::user()->id != $user_id) {
        return ResponseHelper::error(null, null, 'Unauthorized', 401);
    }
    $validator = Validator::make($request->all(), [
        'name' => ['required', 'string'],
        'email' => ['required', 'email'],
        'phone' => ['required', 'string'],
        'gender' => ['required', 'string'],
        'photo' => ['nullable', 'string'],
        'address' => ['required', 'string'],
        'wallet' => ['numeric'],
    ]);

    if ($validator->fails()) {
        return ResponseHelper::error($validator->errors()->all(), null, 'Validation failed', 422);
    }

    $user = User::find($user_id);

    if (!$user) {
        return ResponseHelper::error([], null, 'User not found', 404);
    }

    $user->update($request->all());

    return ResponseHelper::updated($user, 'User updated');
}
public function userForgotPassword(Request $request): JsonResponse
{
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Delete all old codes that user sent before.
        ResetCodePassword::where('email', $request->email)->delete();

        // Generate random code
        $data['code'] = mt_rand(100000, 999999);

        // Create a new code
        $codeData = ResetCodePassword::create($data);

        // Send email to user
        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return response()->json([
            'message' => 'code.sent'
        ], Response::HTTP_OK);
}
public function userCheckCode(Request $request): JsonResponse
{
        $request->validate([
            'code' => 'required|string|exists:reset_code_passwords',
        ]);

        // Find the code
        $passwordReset = ResetCodePassword::where('code', $request->code)->first();

        // Check if it is not expired: the time is one hour
        if ($passwordReset->created_at->addHour()->isPast()) {
            $passwordReset->delete();
            return response()->json(['message' => 'passwords code has expired'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => 'passwords code is valid'
        ], Response::HTTP_OK);
}
public function getAllUsers()
{
        $users = User::all();
        return ResponseHelper::success($users, 'All Users Are retrieved');
}
public function userResetPassword(Request $request): JsonResponse
{
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:8',
            'c_password' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Find the code
        $passwordReset = ResetCodePassword::where('code', $request->code)->first();

        // Check if it is not expired: the time is one hour
        if ($passwordReset->created_at->addHour()->isPast()) {
            $passwordReset->delete();
            return response()->json(['message' => 'passwords code_is_expire'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Find user's email
        $user = User::where('email', $passwordReset->email)->first();
        // Update user password
        $user->password = Hash::make($request->password);
        $user->save();
        // Delete current code
        $passwordReset->delete();

        return response()->json(['message' => 'password has been successfully reset'], Response::HTTP_OK);
}
public function requestVerifyAccount(Request $request): JsonResponse
{
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Delete all old codes that user sent before.
        ResetCodePassword::where('email', $request->email)->delete();

        // Generate random code
        $data['code'] = mt_rand(100000, 999999);

        // Create a new code
        $codeData = ResetCodePassword::create($data);

        // Send email to user
        Mail::to($request->email)->send(new VerifyAccount($codeData->code));

        return response()->json([
            'message' => 'code.sent'
        ], Response::HTTP_OK);
}
public function verifyAccount(Request $request): JsonResponse
{
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Find the code
        $code = ResetCodePassword::where('code', $request->code)->first();

        // Check if it is not expired: the time is one hour
        if ($code->created_at->addHour()->isPast()) {
            $code->delete();
            return response()->json(['message' => 'passwords code is expired'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Find user's email
        $user = User::where('email', $code->email)->first();

        $user->email_verified_at = now();
        $user->save();

        $tokenResult = $user->createToken('personal Access Token')->accessToken;

        $data = [
            'message' => 'Your Email Is Verified!',
            'user' => $user,
            'TokenType' => 'Bearer',
        ];

        return response()->json($data, Response::HTTP_OK);
}
public function changeRole(Request $request, $user_id)
{
        try {
            if (!auth()->check()) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }

            if (Auth::user()->role !== '2') {
                return ResponseHelper::error([], null, 'Only the admin can change roles', 403);
            }

            $user = User::find($user_id);

            if (!$user) {
                return ResponseHelper::error([], null, 'User not found', 404);
            }

            $user->role = $request->new_role;
            $user->save();

            return ResponseHelper::success($user, null, 'User Role Updated Successfully', 200);
        } catch (\Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
}
public function chargeWallet(Request $request, $user_id)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $user = User::findOrFail($user_id);
        $oldWallet = $user->wallet;
        $walletToAdd = $request->input('wallet');
        $newWallet = $oldWallet + $walletToAdd;
        $user->wallet = $newWallet;
        $user->save();

        return ResponseHelper::success('Wallet charged successfully', ['wallet' => $newWallet]);
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getWallet($user_id)
{
    try {
        $user = User::findOrFail($user_id);
        if (($user->id !== Auth::user()->id) && ($user->role !== '2' || Auth::user()->role !== '2')) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $wallet = $user->wallet;

        return ResponseHelper::success(['wallet' => $wallet], 200, 'Wallet retrieved successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'User not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function getAllWallets()
{
    $user = Auth::user();
    if ($user->role !== '2'){
        return ResponseHelper::error([], null, 'Unauthorized', 401);
    }
    $users = User::all();
    $walletData = [];

    foreach ($users as $user) {
        if ($user->id !== Auth::user()->id && $user->role !== '2') {
            $walletData[] = [
                'user_id' => $user->id,
                'wallet' => $user->wallet,
            ];
        }
    }

    return ResponseHelper::success(['wallets' => $walletData] , 200, 'All wallets retrieved successfully');
}
public function removeWallet($user_id)
{
        $user = User::findOrFail($user_id);
        if ($user->id !== Auth::user()->id && $user->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }
        $user->wallet = 0;
        $user->save();

        return ResponseHelper::success('Wallet deleted successfully');
}
public function addDonation(Request $request)
{
        try {
            $validator = Validator::make($request->all(), [
                'balance' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            if (!auth()->check()) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);}

            $loggedUser = Auth::user();
            if ($loggedUser->wallet < $request->input('balance')) {
                return ResponseHelper::error([], null, 'Insufficient balance', 400);}
            if ($loggedUser->wallet <= 0) {
                    return ResponseHelper::error([], null, 'Wallet balance is zero or negative', 400);}
            $donationData = $request->only('balance');
            $donationData['donation_date'] = now()->format('Y-m-d H:i:s');
            $donationData['user_id'] = $loggedUser->id;

            $donation = Donation::create($donationData);
            $loggedUser->wallet -= $request->input('balance');
            $loggedUser->save();

            return ResponseHelper::created($donation, 'Donation added successfully');
        } catch (Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
}
public function updateDonation(Request $request, $donation_id)
{
        try {
            $validator = Validator::make($request->all(), [
                'balance' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $donation = Donation::findOrFail($donation_id);
            $userData = $request->all();

            if ($donation->user_id !== Auth::user()->id) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }


            $loggedUser = Auth::user();
            if ($loggedUser->wallet < $userData['balance']) {
                return ResponseHelper::error([], null, 'Insufficient balance', 400);
            }

            $donationData = [
                'balance' => $userData['balance'],
            ];

            $donation->update($donationData);


            $loggedUser->wallet += $donation->balance;
            $loggedUser->wallet -= $userData['balance'];
            $loggedUser->save();

            return ResponseHelper::updated($donation, 'Donation updated successfully');
        } catch (ModelNotFoundException $exception) {
            return ResponseHelper::error([], null, 'Donation not found', 404);
        } catch (Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
}
public function getUserDonations($user_id)
{
        try {
            $loggedInUser = Auth::user();

            if ($loggedInUser->id != $user_id && $loggedInUser->role != 2) {
                return ResponseHelper::error([], null, 'Unauthorized', 401);
            }

            $user = User::findOrFail($user_id);
            $donations = $user->donations;

            return ResponseHelper::success($donations, 'User donations retrieved successfully');
        } catch (ModelNotFoundException $exception) {
            return ResponseHelper::error([], null, 'User not found', 404);
        } catch (Throwable $th) {
            return ResponseHelper::error([], null, $th->getMessage(), 500);
        }
}
public function deleteDonation($donation_id)
{
    try {
        $donation = Donation::findOrFail($donation_id);

        if ($donation->user_id !== Auth::user()->id) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $donation->delete();

        return ResponseHelper::success([], 'Donation deleted successfully');
    } catch (ModelNotFoundException $exception) {
        return ResponseHelper::error([], null, 'Donation not found', 404);
    } catch (Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function empReq(Request $request)
{
    try {
        if (!Auth::check()) {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        $loggedInUser = Auth::user();

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
            return ResponseHelper::error([], null, $validator->errors()->all(), 422);
        }

        if ($loggedInUser->role !== '1') {
            return ResponseHelper::error([], null, 'You cannot make this request.', 400);
        }

        $employeeData = $request->only(['age', 'job_title', 'start_time', 'end_time']);

        $loggedInUser->employee()->updateOrCreate([], $employeeData);
        $loggedInUser->load('employee');

        return ResponseHelper::success($loggedInUser, null, 'Employee Request Processed Successfully', 201);
    } catch (\Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
public function approveUser(User $user)
{
    try {
        if (Auth::user()->role !== '2') {
            return ResponseHelper::error([], null, 'Unauthorized', 401);
        }

        if ($user->employee) {
            $user->employee->update([
                'is_verified' => true,
            ]);
            $user->update([
                'role' => '4'
            ]);
        }

        $emailSent = Mail::to($user->email)->send(new JobApplicationApprovedEmail($user));

        if ($emailSent) {
            return ResponseHelper::success([], null, 'Email has been sent successfully', 200);
        } else {
            return ResponseHelper::error([], null, 'Failed to send email', 500);
        }
    } catch (\Throwable $th) {
        return ResponseHelper::error([], null, $th->getMessage(), 500);
    }
}
}
