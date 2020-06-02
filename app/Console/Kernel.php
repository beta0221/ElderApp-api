<?php

namespace App\Console;

use App\Transaction;
use App\User;
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



        //檢查過期會員
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

        
        //生日會員
        $schedule->call(function(){
            Log::info('birthday check');
            date_default_timezone_set('Asia/Taipei');
            $users = User::whereMonth('birthdate',date('m'))->whereDay('birthdate',date('d'))->get();
            foreach ($users as $user) {
                Log::info('today is user '.$user->id.' birthday');
                // $user->updateWallet(true,800);
                // $tran_id = time() . rand(10,99);
                // Transaction::create([
                //     'tran_id'=>$tran_id,
                //     'user_id'=>$user->id,
                //     'event' =>'壽星生日禮',
                //     'amount'=>800,
                //     'target_id'=>0,
                //     'give_take'=>true,
                // ]);
            }
        })->dailyAt('08:00');

        // $schedule->call(function(){
        //     Log::info('schedule test every minute');
        // })->everyMinute();


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
