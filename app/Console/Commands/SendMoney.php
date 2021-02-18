<?php

namespace App\Console\Commands;

use App\Jobs\SendMoney as AppSendMoney;
use App\PayDate;
use App\User;
use Illuminate\Console\Command;

class SendMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:money {from} {to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send money to users between two user_id';

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

        //發送:續會獎勵

        $from = $this->argument('from');
        $to = $this->argument('to');



        $user_id_array = PayDate::where('id','>=',$from)
        ->where('id','<=',$to)
        ->pluck('user_id');

        $users = User::whereIn('id',$user_id_array)->get();

        // $users = User::where('id','>=',$from)
        // ->where('id','<=',$to)
        // ->where('valid',1)
        // ->get();

        foreach ($users as $user) {
            $count = PayDate::where('user_id',$user->id)->count();
            if($count <= 1){ continue; }
            $this->info('user:' . $user->name . '(' . $user->id . ')');
            AppSendMoney::dispatch($user,0,'')->onQueue('sendMoney');
        }

        $this->info('from:'.$from.', to:'.$to.' (success)');

        
    }
}
