<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class RewardLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reward_level')->insert([
            'reward'=>300,
        ]);

        DB::table('reward_level')->insert([
            'reward'=>500,
        ]);

        DB::table('reward_level')->insert([
            'reward'=>800,
        ]);
    }
}
