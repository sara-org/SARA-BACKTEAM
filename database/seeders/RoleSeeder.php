<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $sara = User::create([
            'name' => 'Sara',
            'email' => 'sara@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0998750053',
            'address' =>'Damascus',
            'photo' => 'admin.jpg',
            'gender'=> 'female',
            'role'=> '2'
        ]);

        $ahmad = User::create([
            'name' => 'Ahmad',
            'email' => 'ahmad@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0986646308',
            'address' => 'Damascus',
            'photo' => 'doctor1.jpg',
            'gender'=> 'male',
            'role'=> '3'
        ]);

        Doctor::create([
            'age' => '30',
            'address' => 'Damascus',
            'user_id' => $ahmad->id
        ]);

        $omar = User::create([
            'name' => 'Omar',
            'email' => 'omar@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '0967436775',
            'address' => 'Damascus',
            'photo' => 'doctor2.jpg',
            'gender'=> 'male',
            'role'=> '3',
        ]);

        Doctor::create([
            'age' => '44',
            'address' => 'Damascus',
            'user_id' => $omar->id
        ]);
    }
}
