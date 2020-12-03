<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:user {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete user row and every related rows in every table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


        //comment user_id 刪
        //post user_id 刪
        //event_users user_id 刪 event people -1
        //events owner_id  改成 NULL
        //frequently_event_user user_id 刪
        //location_manager user_id 刪
        //location user_id 改 0
        //order_delievery user_id 忽略
        //order_details user_id 刪
        //order user_id 刪
        //pay_dates user_id 刪
        //post_like user_id 刪  post like -1
        //products firm_id 改 0
        //role_user user_id 刪
        //transactions user_id 刪
        //user_group user_id 刪
        //user_pushtoken user_id 刪
        //user id 刪

    private $db = [
        'comment'=>'user_id',
        'post'=>'user_id',
        'event_users'=>'user_id',
        'events'=>'owner_id',
        'frequently_event_user'=>'user_id',
        'location_manager'=>'user_id',
        'locations'=>'user_id',
        'order_delievery'=>'user_id',
        'order_details'=>'user_id',
        'orders'=>'user_id',
        'pay_dates'=>'user_id',
        'post_like'=>'user_id',
        'products'=>'firm_id',
        'role_user'=>'user_id',
        'transactions'=>'user_id',
        'user_group'=>'user_id',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $user_id = $this->argument('user_id');

        foreach ($this->db as $table => $column) {
            if($row = DB::table($table)->where($column,$user_id)->first()){
                $this->info('Delete Fail:User has related row in table ' . $table);
                return;
            }
        }

        $this->info('Delete Success:Delete user id ' . $user_id);
    }
}
