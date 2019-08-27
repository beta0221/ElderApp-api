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
        $user->name = 'user';
        $user->email = 'user@user.com';
        $user->password = 'user';
        $user->save();
        $user->roles()->attach($role_user);

        $admin = new User();
        $admin->name = 'admin';
        $admin->email = 'admin@admin.com';
        $admin->password = 'admin';
        $admin->save();
        $admin->roles()->attach($role_admin);


    }
}
