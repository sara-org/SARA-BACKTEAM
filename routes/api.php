<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnimalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request)
     {
        return $request->user();
    });



    Route::post('/animal/add',[AnimalController::class,'addAnimal']);
    Route::post('/animal/update/{animal_id}',[AnimalController::class,'updateAnimal']);
    Route::get('/animal/getall',[AnimalController::class,'getAllAnimals']);
    Route::delete('/animal/delete/{animal_id}',[AnimalController::class,'deleteAnimal']);
    Route::post('/user/change-role/{user_id}', [UserController::class, 'changeRole']);
    
});
    
Route::post('/user/signup', [UserController::class, 'signUp']);
Route::post('/user/signin', [UserController::class, 'login']);
Route::post('/user/logout', [UserController::class, 'logout']);
Route::post('/password/email', [UserController::class, 'userForgotPassword']);
Route::post('/password/code/check', [UserController::class, 'userCheckCode']);
Route::post('/password/reset', [UserController::class, 'userResetPassword']);
Route::post('/email/requestverify', [UserController::class, 'requestVerifyAccount']);
Route::post('/email/verify', [UserController::class, 'verifyAccount']);
