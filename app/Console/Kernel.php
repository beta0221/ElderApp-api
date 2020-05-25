<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function(){
            Log::info('expire check');
            date_default_timezone_set('Asia/Taipei');
            $now = strtotime(date('Y-m-d'));
            $users = DB::select("SELECT * FROM users WHERE UNIX_TIMESTAMP(expiry_date) < $now AND valid = 1");
            foreach ($users as $user) {
                Log::channel('expirelog')->info('user '.$user->name.'(' . $user->id .') expired');    
            }
            DB::update("UPDATE users SET valid = 0 WHERE UNIX_TIMESTAMP(expiry_date) < $now AND valid = 1");
        })->dailyAt('03:00');

        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
