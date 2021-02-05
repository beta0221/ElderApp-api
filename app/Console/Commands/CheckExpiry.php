<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $now = strtotime(date('Y-m-d'));
        $users = DB::select("SELECT * FROM users WHERE UNIX_TIMESTAMP(expiry_date) < $now AND valid = 1");
        foreach ($users as $user) {
            Log::channel('expirelog')->info('user '.$user->name.'(' . $user->id .') expired');    
        }
        DB::update("UPDATE users SET valid = 0 WHERE UNIX_TIMESTAMP(expiry_date) < $now AND valid = 1");
    }
}
