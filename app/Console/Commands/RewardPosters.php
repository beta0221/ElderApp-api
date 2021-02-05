<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Post;
use App\User;
use Illuminate\Support\Facades\Log;

class RewardPosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reward:posters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reward posters.';

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
        $postList = Post::whereDate('created_at',date('Y-m-d',strtotime("-2 days")))->get();
        foreach ($postList as $post) {
            if($user = User::find($post->user_id)){
                $reward = $post->likes + $post->comments + 2;
                $user->update_wallet_with_trans(User::INCREASE_WALLET,$reward,'社群活躍獎勵');
                $user->increaseRank($reward);
                Log::info("User $user->name gets $reward reward");
            }
        }
    }
}
