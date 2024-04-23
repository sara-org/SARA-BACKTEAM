<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;


class RoleSeeder extends Seeder
{
    public function run()
    {
        $userRole = Role::create(['name' => 'user']);
        $adminRole = Role::create(['name' => 'admin']);
        $doctorRole = Role::create(['name' => 'doctor']);
        $employeeRole = Role::create(['name' => 'employee']);

        $adminUser = User::create([
            'name' => 'Sara',
            'email' => 'sara@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '0998750053',
            'address' =>'Damascus',
            'photo' => 'admin.jpg',
            'gender'=> 'female',
            'role_id'=> $adminRole->role_id
        ]);

        $doctorUser1 = User::create([
            'name' => 'Ahmad',
            'email' => 'Ahmad@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '0986646308',
            'address' => 'Damascus',
            'photo' => 'doctor1.jpg',
            'gender'=> 'male',
            'role_id'=> $doctorRole->role_id
        ]);

        $doctorUser2 = User::create([
            'name' => 'Omar',
            'email' => 'omar@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '0967436775',
            'address' => 'Damascus',
            'photo' => 'doctor2.jpg',
            'gender'=> 'male',
            'role_id'=> $doctorRole->role_id
        ]);

    }
}
