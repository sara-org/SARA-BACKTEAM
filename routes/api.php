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

    // Users 
    Route::post('/user/change-role/{user_id}', [UserController::class, 'changeRole']);
    
    // Animals
    Route::post('/animal/add',[AnimalController::class,'addAnimal']);
    Route::post('/animal/update/{animal_id}',[AnimalController::class,'updateAnimal']);
    Route::get('/animals/getall',[AnimalController::class,'getAllAnimals']);
    Route::delete('/animal/delete/{animal_id}',[AnimalController::class,'deleteAnimal']);
    // Animal Types
    Route::post('/animaltype/add',[AnimalTypeController::class,'addAnimalType']);
    Route::post('/animaltype/update/{animaltype_id}',[AnimalTypeController::class,'updateAnimalType']);
    Route::get('/animaltypes/getall',[AnimalTypeController::class,'getAllAnimalsTypes']);
    Route::delete('/animaltype/delete/{animaltype_id}',[AnimalTypeController::class,'deleteAnimalType']);
   // Departments
   Route::post('/department/add',[DepartmentController::class,'addDepartment']);
   Route::post('/department/update/{department_id}',[DepartmentController::class,'updateDepartment']);
   Route::get('/departments/getall',[DepartmentController::class,'getAllDepartments']);
   Route::delete('/department/delete/{department_id}',[DepartmentController::class,'deleteDepartment']);
   Route::get('/animal-types/getType/{id}',[AnimalTypeController::class,'getAnimalsByType']);
     // Donations
     Route::post('/user/donation/add/{user_id}', [UserController::class, 'addDonation']);
     Route::post('/user/donation/update/{donation_id}', [UserController::class, 'updateDonation']);
     Route::get('/user/donations/user/{user_id}', [UserController::class, 'getUserDonations']);
     Route::delete('/user/donation/delete/{donation_id}', [UserController::class, 'deleteDonation']);
    // Sponcerships
    Route::post('/user/sponcership/add', [UserController::class, 'addSponcership']);
    Route::post('/user/sponcership/update/{sponcership_id}', [UserController::class, 'updateSponcership']);
    Route::get('/user/sponcerships/user/{user_id}', [UserController::class, 'getUserSponcerships']);
    Route::delete('/user/sponcership/delete/{sponcership_id}', [UserController::class, 'deleteSponcership']);
    // Adoptions
    Route::post('/user/adoption/add', [UserController::class, 'addAdoption']);
    Route::post('/user/adoption/update/{adoption_id}', [UserController::class, 'updateAdoption']);
    Route::get('/user/adoptions/user/{user_id}', [UserController::class, 'getUserAdoptions']);
    Route::delete('/user/adoption/delete/{adoption_id}', [UserController::class, 'deleteAdoption']);


});
    
Route::post('/user/signup', [UserController::class, 'signUp']);
Route::post('/user/signin', [UserController::class, 'login']);
Route::post('/user/logout', [UserController::class, 'logout']);
Route::post('/password/email', [UserController::class, 'userForgotPassword']);
Route::post('/password/code/check', [UserController::class, 'userCheckCode']);
Route::post('/password/reset', [UserController::class, 'userResetPassword']);
Route::post('/email/requestverify', [UserController::class, 'requestVerifyAccount']);
Route::post('/email/verify', [UserController::class, 'verifyAccount']);
