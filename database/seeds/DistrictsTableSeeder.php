<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districtList = ['桃園','中壢','平鎮','八德','龜山','蘆竹','大園','觀音','新屋','楊梅','龍潭','大溪','復興'];

        foreach ($districtList as $district) {
            DB::table('districts')->insert([
                'group'=>'district',
                'name'=>$district
            ]);
        }

    }
}
