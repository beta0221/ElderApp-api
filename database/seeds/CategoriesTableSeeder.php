<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $catList = ['歡樂旅遊','樂活才藝','健康課程','社會服務','天使培訓','長照據點','大型活動'];

        foreach ($catList as $cat) {

            DB::table('categories')->insert([
                'name'=>$cat,
            ]);
        }
        

        
    }
}
