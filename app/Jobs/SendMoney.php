<?php

namespace App\Jobs;

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
        sleep(2);
        $this->user->rewardInviter();
        $this->user->rewardGroupMembers();
        // $this->user->update_wallet_with_trans(User::INCREASE_WALLET,$this->amount,$this->event);   
        // NotifyAppUser::dispatch($this->user->id,'銀髮學院祝您新年快樂~','您將獲得-新年紅包800點');
    }
}
