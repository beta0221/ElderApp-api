<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\NotifyAppUser;
use App\User;
use Carbon\Carbon;

class CheckExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiry.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('expire check');
        date_default_timezone_set('Asia/Taipei');

        $today = Carbon::today();
        $users = User::whereDate('expiry_date','<',$today)->where('valid',1)->get();
        // $users = DB::select("SELECT * FROM users WHERE UNIX_TIMESTAMP(expiry_date) < $now AND valid = 1");
        foreach ($users as $user) {
            Log::channel('expirelog')->info('user '.$user->name.'(' . $user->id .') expired');
            NotifyAppUser::dispatch($user->id,'會員效期通知','您的會員效期已到期。');
            $user->valid = 0;
            $user->pay_status = 0;
            try {
                $user->save();
            } catch (\Throwable $th) {
                Log::channel('expirelog')->info('Error on user '.$user->name.'(' . $user->id .')');
            }
            
        }
    }
}
