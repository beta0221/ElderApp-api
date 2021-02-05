<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\User;

class CheckBirthdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:birthdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check birthdate.';

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
        Log::info('birthday check');
        date_default_timezone_set('Asia/Taipei');
        $users = User::whereMonth('birthdate',date('m'))->whereDay('birthdate',date('d'))->where('valid',1)->get();
        foreach ($users as $user) {
            Log::channel('birthdaylog')->info('today is user '.$user->name.'('.$user->id.')'.' birthday');
            $user->update_wallet_with_trans(User::INCREASE_WALLET,800,'壽星生日禮');
        }
    }
}
