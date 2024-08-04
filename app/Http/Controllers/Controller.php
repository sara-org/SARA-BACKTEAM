<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
 // $latestAppointments = Appointment::where('doctor_id', $doctor->id)
    //     ->where('day', $request->day)
    //     ->where('reserved_time', $request->reserved_time)
    //     ->where('date', '>=', $currentTime->subDays(7)->format('Y-m-d'))
    //     ->get();

    // if ($latestAppointments->isNotEmpty()) {
    //     return ResponseHelper::error([], null, 'Another appointment exists within the same time slot in the last 7 days', 422);
    // }
