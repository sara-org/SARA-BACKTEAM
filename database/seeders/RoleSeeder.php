<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;


class RoleSeeder extends Seeder
{
    public function run()
    {

        User::create([
            'name' => 'Sara',
            'email' => 'sara@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '0998750053',
            'address' =>'Damascus',
            'photo' => 'admin.jpg',
            'gender'=> 'female',
            'role'=> '2'
        ]);

        User::create([
            'name' => 'Ahmad',
            'email' => 'Ahmad@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '0986646308',
            'address' => 'Damascus',
            'photo' => 'doctor1.jpg',
            'gender'=> 'male',
            'role'=> '3'
        ]);

        User::create([
            'name' => 'Omar',
            'email' => 'omar@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '0967436775',
            'address' => 'Damascus',
            'photo' => 'doctor2.jpg',
            'gender'=> 'male',
            'role'=> '3',
        ]);

    }
}
