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
            'reward'=>10,
        ]);

        DB::table('reward_level')->insert([
            'reward'=>30,
        ]);

        DB::table('reward_level')->insert([
            'reward'=>50,
        ]);

        DB::table('reward_level')->insert([
            'reward'=>100,
        ]);
    }
}
