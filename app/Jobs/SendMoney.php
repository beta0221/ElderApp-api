<?php

namespace App\Jobs;

use App\Transaction;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMoney implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $user;
    protected $amount;
    protected $event;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$amount,$event)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->event = $event;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(3);
        $this->user->updateWallet(true,$this->amount);

        $tran_id = time() . rand(10,99);
        Transaction::create([
            'tran_id'=>$tran_id,
            'user_id'=>$this->user->id,
            'event' =>$this->event,
            'amount'=>$this->amount,
            'target_id'=>0,
            'give_take'=>true,
        ]);
    }
}
