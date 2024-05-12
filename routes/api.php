<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AnimalTypeController;

use App\Http\Controllers\DepartmentController;
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

    //user 
    Route::post('/user/change-role/{user_id}', [UserController::class, 'changeRole']);
    
    //animal
    Route::post('/animal/add',[AnimalController::class,'addAnimal']);
    Route::post('/animal/update/{animal_id}',[AnimalController::class,'updateAnimal']);
    Route::get('/animal/getall',[AnimalController::class,'getAllAnimals']);
    Route::delete('/animal/delete/{animal_id}',[AnimalController::class,'deleteAnimal']);
    //animal type
    Route::post('/animaltype/add',[AnimalTypeController::class,'addAnimalType']);
    Route::post('/animaltype/update/{animaltype_id}',[AnimalTypeController::class,'updateAnimalType']);
    Route::get('/animaltypes/getall',[AnimalTypeController::class,'getAllAnimalsTypes']);
    Route::delete('/animaltype/delete/{animaltype_id}',[AnimalTypeController::class,'deleteAnimalType']);
   //department
   Route::post('/department/add',[DepartmentController::class,'addDepartment']);
   Route::post('/department/update/{department_id}',[DepartmentController::class,'updateDepartment']);
   Route::get('/department/getall',[DepartmentController::class,'getAllDepartments']);
   Route::delete('/department/delete/{department_id}',[DepartmentController::class,'deleteDepartment']);
   Route::get('/animal-types/getType/{id}',[AnimalTypeController::class,'getAnimalsByType']);
});
    
Route::post('/user/signup', [UserController::class, 'signUp']);
Route::post('/user/signin', [UserController::class, 'login']);
Route::post('/user/logout', [UserController::class, 'logout']);
Route::post('/password/email', [UserController::class, 'userForgotPassword']);
Route::post('/password/code/check', [UserController::class, 'userCheckCode']);
Route::post('/password/reset', [UserController::class, 'userResetPassword']);
Route::post('/email/requestverify', [UserController::class, 'requestVerifyAccount']);
Route::post('/email/verify', [UserController::class, 'verifyAccount']);
