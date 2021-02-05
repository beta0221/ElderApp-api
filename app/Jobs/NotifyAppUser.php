<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifyAppUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $title;
    protected $body;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$title,$body,$data = null)
    {
        $this->user_id = $user_id;
        $this->title = $title;
        $this->body = $body;
        
        if(is_null($data)){
            $this->data = [
                'updateWallet'=>'true',
                'message'=>$body,
            ];
        }else{
            $this->data = $data;
        }
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$row = DB::table('user_pushtoken')->where('user_id',$this->user_id)->first()){ return; }
        if(!$fcm_token = Redis::get('ELDERAPP_FCM_TOKEN')){ return; }
        $push_token = $row->pushtoken;

        Log::info('fcm_token : ' . $fcm_token);
        Log::info('push_token : ' . $push_token);
        

        $postBody = [
            'message'=>[
                'token'=>$push_token,
                'notification'=>[
                    'title'=>$this->title,
                    'body'=>$this->body,
                ],
                'data'=>$this->data,
            ]
        ];


        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/elderapp-438e0/messages:send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postBody),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $fcm_token,
                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::info(json_encode($err));
        } else {
            Log::info(json_encode($response));
        }


    }
}
