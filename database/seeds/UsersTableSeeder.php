<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_user = Role::where('name', 'user')->first();
        $role_admin  = Role::where('name', 'admin')->first();

        $user = new User();
        $user->id_code = 'user';
        $user->name = 'user';
        $user->email = 'user';
        $user->password = 'user';
        $user->save();
        $user->roles()->attach($role_user);

        $admin = new User();
        $user->id_code = 'admin';
        $admin->name = 'admin';
        $admin->email = 'admin';
        $admin->password = 'admin';
        $admin->save();
        $admin->roles()->attach($role_admin);


    }
}
