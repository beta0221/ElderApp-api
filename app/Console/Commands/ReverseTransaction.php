<?php

namespace App\Console\Commands;

use App\Transaction;
use Illuminate\Console\Command;

class ReverseTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reverse:transaction {from} {to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reverse transaction between two transaction_id';

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
        $from = $this->argument('from');
        $to = $this->argument('to');

        $trans = Transaction::where('id','>=',$from)
        ->where('id','<=',$to)
        ->get();
        
        foreach ($trans as $tran) {
            $this->info('transaction:' . $tran->event . '(' . $tran->id . ')');
            $tran->reverse();
        }

        $this->info('from:'.$from.', to:'.$to.' (success)');

    }
}
