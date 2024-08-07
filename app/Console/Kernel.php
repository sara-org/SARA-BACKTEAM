<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Models\WorkingHours;
use Carbon\Carbon;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

protected function schedule(Schedule $schedule)
{
    // $schedule->call(function () {
    //     $day = Carbon::now()->dayName;


    //     WorkingHours::where('status', 1)
    //         ->where('day', $day)
    //         ->update(['status' => 0]);
    // })->daily();
}
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
