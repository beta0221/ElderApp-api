<?php

namespace App\Console\Commands;

use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOrderExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:orderExpiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '檢查 7 天未領取的訂單 並作廢';

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
        Log::info('order expire check');
        date_default_timezone_set('Asia/Taipei');
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $orders = Order::where('ship_status',Order::STATUS_ARRIVE)
            ->whereDate('created_at','<',$sevenDaysAgo)
            ->get();

        foreach ($orders as $order) {
            Log::channel('order_expirelog')->info('order (' . $order->order_numero . ') is expired');
            $order->voidOrder();
        }

    }
}
