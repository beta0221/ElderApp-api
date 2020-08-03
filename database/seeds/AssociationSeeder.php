<?php

use Illuminate\Database\Seeder;

class AssociationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('association')->insert([
            'name'=>'台北',
        ]);
        DB::table('association')->insert([
            'name'=>'桃園',
        ]);
        DB::table('association')->insert([
            'name'=>'新竹',
        ]);
    }
}
