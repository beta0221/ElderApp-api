<?php

use Illuminate\Database\Seeder;
use App\Role;

class rolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();
        $role->name = 'user';
        $role->description = 'user';
        $role->save();

        $role = new Role();
        $role->name = 'admin';
        $role->description = 'admin';
        $role->save();

        $role = new Role();
        $role->name = 'employee';
        $role->description = 'employee';
        $role->save();
    }
}
