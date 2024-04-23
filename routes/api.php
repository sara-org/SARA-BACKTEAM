<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::post('/user/signup', [UserController::class, 'signUp']);
Route::post('/user/signin', [UserController::class, 'login']);
Route::post('/user/logout', [UserController::class, 'logout']);
Route::post('password/email', [UserController::class, 'userForgotPassword']);
Route::post('password/code/check',[UserController::class, 'userCheckCode']);
Route::post('password/reset', [UserController::class, 'userResetPassword']);
Route::post('email/requestverify', [UserController::class, 'requestVerifyAccount']);
Route::post('email/verify', [UserController::class, 'verifyAccount']);
Route::post('/change-role/{id}', [UserController::class, 'changeRole']);
