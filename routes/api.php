<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AnimalTypeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\RequestController;

/*
|--------------------------------------------------------------------------
| Sara API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request)
    {
        return $request->user();
    });

    // Users
    Route::post('/user/change-role/{user_id}', [UserController::class, 'changeRole']);
    Route::post('/user/update/{user_id}', [UserController::class, 'updateUser']);
    Route::get('/users/all', [UserController::class, 'getAllUsers']);


    // wallet
    Route::post('/user/wallet/charge/{user_id}', [UserController::class, 'chargeWallet']);
    Route::get('/user/wallet/get/{user_id}', [UserController::class, 'getWallet']);
    Route::get('/user/wallets', [UserController::class, 'getAllWallets']);
    Route::delete('/user/wallet/remove/{user_id}', [UserController::class, 'removeWallet']);

     // Animals
    Route::post('/animal/add',[AnimalController::class,'addAnimal']);
    Route::post('/animal/update/{animal_id}',[AnimalController::class,'updateAnimal']);
    Route::get('/animals/getall',[AnimalController::class,'getAllAnimals']);
    Route::get('/animal/get/{animal_id}',[AnimalController::class,'getAnimal']);
    Route::delete('/animal/delete/{animal_id}',[AnimalController::class,'deleteAnimal']);
    Route::get('/animal-types/getType/{id}',[AnimalTypeController::class,'getAnimalsByType']);

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

    // Donations
   Route::post('/user/donation/add', [UserController::class, 'addDonation']);
   Route::post('/user/donation/update/{donation_id}', [UserController::class, 'updateDonation']);
   Route::get('/user/donations/user/{user_id}', [UserController::class, 'getUserDonations']);
   Route::delete('/user/donation/delete/{donation_id}', [UserController::class, 'deleteDonation']);

    // Sponcerships
   Route::post('/user/sponcership/req', [EmployeeController::class, 'reqSponcership']);
   Route::post('/user/sponcership/updatespon/{sponcership_id}', [EmployeeController::class, 'ApproveSponcership']);
   Route::get('/user/sponcerships/user/{user_id}', [EmployeeController::class, 'getUserSponcerships']);
   Route::delete('/user/sponcership/delete/{sponcership_id}', [EmployeeController::class, 'deleteSponcership']);
    // Adoptions
   Route::post('/user/adoption/Req', [EmployeeController::class, 'ReqAdoption']);
   Route::post('/user/adoption/updatereq/{adoption_id}', [EmployeeController::class, 'ApproveAdoption']);
   Route::get('/user/adoptions/user/{user_id}', [EmployeeController::class, 'getUserAdoptions']);
   Route::delete('/user/adoption/delete/{adoption_id}', [EmployeeController::class, 'deleteAdoption']);
   // employees
   Route::post('/user/employee/add', [EmployeeController::class, 'addEmployee']);
   Route::post('/user/employee/update/{employee_id}', [EmployeeController::class, 'updateEmployee']);
   Route::get('/user/employee/get/{employee_id}', [EmployeeController::class, 'getEmployee']);
   Route::get('/user/employees/getall', [EmployeeController::class, 'getAllEmployees']);
   Route::delete('/user/employee/delete/{employee_id}', [EmployeeController::class, 'deleteEmployee']);
   // feedings
   Route::post('/user/employee/feeding/add', [EmployeeController::class, 'addFeeding']);
   Route::post('/user/employee/feeding/update/{feeding_id}', [EmployeeController::class, 'updateFeeding']);
   Route::get('/user/employee/feeding/{user_id}', [EmployeeController::class, 'getUserFeedings']);
   Route::get('/user/unfed-departments', [EmployeeController::class, 'getUnfedDepartments']);
   Route::get('/user/employee/feedings/all', [EmployeeController::class, 'getAllFeedings']);
   Route::delete('/user/feeding/delete/{feeding_id}', [EmployeeController::class, 'deleteFeeding']);
   // vaccinations
   Route::post('/user/employee/vaccination/add', [EmployeeController::class, 'addVaccination']);
   Route::post('/user/employee/vaccination/update/{vaccination_id}', [EmployeeController::class, 'updateVaccination']);
   Route::get('/user/employee/vaccination/{user_id}', [EmployeeController::class, 'getUserVaccinations']);
   Route::get('/user/employee/vaccinations/all', [EmployeeController::class, 'getAllVaccinations']);
   Route::delete('/user/vaccination/delete/{vaccination_id}', [EmployeeController::class, 'deleteVaccination']);
   Route::get('/user/unVac-departments', [EmployeeController::class, 'getUnVacDepartments']);

   //doctors
   Route::post('/user/doctor/add', [DoctorController::class, 'addDoctor']);
   Route::post('/user/doctor/update/{doctor_id}', [DoctorController::class, 'updateDoctor']);
   Route::get('/user/doctor/get/{doctor_id}', [DoctorController::class, 'getDoctor']);
   Route::get('/user/doctors/getall', [DoctorController::class, 'getAllDoctors']);
   Route::delete('/user/doctor/delete/{doctor_id}', [DoctorController::class, 'deleteDoctor']);
  //medical_records
   Route::post('/user/doctor/medical-record/add', [DoctorController::class, 'addMedicalRecord']);
   Route::post('/user/doctor/medical-record/update/{id}', [DoctorController::class, 'updateMedicalRecord']);
   Route::get('/user/doctors/medical-records/get/{id}', [DoctorController::class,'getMedicalRecord']);
   Route::get('/user/doctors/medical-records/all', [DoctorController::class,'getAllMedicalRecords']);
   Route::delete('/user/doctor/medical-record/delete/{id}', [DoctorController::class, 'deleteMedicalRecord']);
  //---------// emergencies
   Route::post('/user/emergency/add', [EmergencyController::class, 'addEmergency']);
   Route::post('/user/emergency/update/{emergency_id}', [EmergencyController::class, 'updateEmergency']);
   Route::get('/user/emergency/get/{emergency_id}', [EmergencyController::class, 'getEmergencyById']);
   Route::get('/user/emergency/getall', [EmergencyController::class, 'getAllEmergencies']);
   Route::delete('/user/emergency/delete/{emergency_id}', [EmergencyController::class, 'deleteEmergency']);
  //---------// User Emergencies
   Route::post('/user/useremergency/add', [EmergencyController::class, 'addUserEmergency']);
   Route::post('/user/useremergency/update/{user_emergency_id}', [EmergencyController::class, 'updateUserEmergency']);
   Route::get('/user/useremergency/get/{user_emergency_id}', [EmergencyController::class, 'getUserEmergencyById']);
   Route::get('/user/useremergency/getall', [EmergencyController::class, 'getAllUserEmergencies']);
   Route::delete('/user/useremergency/delete/{user_emergency_id}', [EmergencyController::class, 'deleteUserEmergency']);
   // posts
   Route::post('/user/post/add', [PostController::class, 'addPost']);
   Route::get('/user/posts', [PostController::class, 'getAllPosts']);
   Route::get('/user/post/get/{post_id}', [PostController::class, 'getPostById']);
   Route::post('/user/post/update/{post_id}', [PostController::class, 'updatePost']);
   Route::delete('/user/post/delete/{post_id}', [PostController::class, 'deletePost']);
   //comments
   Route::post('/user/comment/add', [CommentController::class, 'addComment']);
   Route::post('/user/comment/update/{comment_id}', [CommentController::class, 'updateComment']);
   Route::get('/user/comment/get/{comment_id}', [CommentController::class, 'getCommentById']);
   Route::get('/user/postcomments/get/{post_id}', [CommentController::class, 'getPostComments']);
   Route::get('/user/usercomments', [CommentController::class, 'getUserComments']);
   Route::get('/user/comments', [CommentController::class, 'getAllComments']);
   Route::delete('/user/comment/delete/{comment_id}', [CommentController::class, 'deleteComment']);
   //likes
   Route::post('/user/likes', [LikeController::class, 'likePost']);
   Route::delete('/user/unlikes/{id}', [LikeController::class, 'unlikePost']);
   // center texts
   Route::post('/user/admin/text/add', [CenterController::class, 'addText']);
   Route::post('/user/admin/text/update/{centerinfo_id}', [CenterController::class, 'updateText']);
   Route::get('/user/admin/text/get/{centerinfo_id}', [CenterController::class, 'getText']);
   Route::delete('/user/admin/text/delete/{centerinfo_id}', [CenterController::class, 'deleteText']);
    // center images
    Route::post('/user/admin/image/add', [CenterController::class, 'addImage']);
    Route::post('/user/admin/image/update/{centerinfo_id}', [CenterController::class, 'updateImage']);
    Route::get('/user/admin/image/get/{centerinfo_id}', [CenterController::class, 'getImage']);
    Route::delete('/user/admin/image/delete/{centerinfo_id}', [CenterController::class, 'deleteImage']);
    //working times
    Route::post('/user/doctor/working-hours/add', [DoctorController::class, 'addWorkingHours']);
    Route::post('/user/doctor/working-hours/update/{id}', [DoctorController::class, 'updateWorkingHours']);
    Route::get('/user/doctor/working-hours/get', [DoctorController::class, 'getWorkingHours']);
    Route::delete('/user/doctor/working-hours/delete/{id}', [DoctorController::class, 'deleteWorkingHours']);
    // appointments
    Route::post('/user/appointments/add', [DoctorController::class, 'addAppointment']);
    //----------//
    Route::post('/user/appointments/update/{id}', [DoctorController::class, 'updateAppointment']);
    Route::get('/user/appointments/getdocapp', [DoctorController::class, 'getAppointmentsForDoctorAndDay']);
    Route::get('/user/appointments/getdayapp', [DoctorController::class, 'getAppointmentsForDay']);
    Route::get('/user/appointment/getapp/{id}', [DoctorController::class, 'getAppointmentById']);
    Route::delete('/user/appointments/delete/{id}', [DoctorController::class, 'deleteAppointment']);

    //requests
    Route::post('/user/request/add', [RequestController::class, 'addRequest']);
    Route::post('/user/request/update/{id}', [RequestController::class, 'updateRequest']);
    Route::get('/user/request/get/{id}', [RequestController::class, 'getRequest']);
    Route::get('/user/requests', [RequestController::class, 'getAllRequests']);
    Route::delete('/user/request/delete/{id}', [RequestController::class, 'deleteRequest']);
    //empReq
    Route::post('/user/emprequest/add', [RequestController::class, 'addEmpreq']);
    Route::post('/user/emprequest/update/{id}', [RequestController::class, 'updateEmpReq']);
   //--------//
    Route::post('/user/emprequest/updatestatus/{id}', [RequestController::class, 'updateEmpReqStatus']);
    Route::get('/user/emprequest/get/{id}', [RequestController::class, 'getEmpreq']);
    Route::get('/user/emprequests', [RequestController::class, 'getAllEmpreqs']);
    Route::delete('/user/emprequest/delete/{id}', [RequestController::class, 'deleteEmpreq']);
    //-------// docreq
    Route::post('/user/docrequest/add', [RequestController::class, 'addDocreq']);
    Route::post('/user/docrequest/update/{id}', [RequestController::class, 'updateDocReq']);
    Route::post('/user/docrequest/updatestatus/{id}', [RequestController::class, 'updateDocReqStatus']);
    Route::get('/user/docrequest/get/{id}', [RequestController::class, 'getDocreq']);
    Route::get('/user/docrequests', [RequestController::class, 'getAllDocreqs']);
    Route::delete('/user/docrequest/delete/{id}', [RequestController::class, 'deleteDocreq']);
});
Route::post('/user/signup', [UserController::class, 'signUp']);
Route::post('/user/signin', [UserController::class, 'login']);
Route::post('/user/logout', [UserController::class, 'logout']);
Route::post('/password/email', [UserController::class, 'userForgotPassword']);
Route::post('/password/code/check', [UserController::class, 'userCheckCode']);
Route::post('/password/reset', [UserController::class, 'userResetPassword']);
Route::post('/email/requestverify', [UserController::class, 'requestVerifyAccount']);
Route::post('/email/verify', [UserController::class, 'verifyAccount']);
