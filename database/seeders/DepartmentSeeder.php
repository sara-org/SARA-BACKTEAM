<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{

    public function run(): void
    {
        Department::create(['id' => 1, 'name' => 'Department 1', 'number' => 10]);
        Department::create(['id' => 2, 'name' => 'Department 2', 'number' => 20]);
        Department::create(['id' => 3, 'name' => 'Department 3', 'number' => 30]);
        Department::create(['id' => 4, 'name' => 'Department 4', 'number' => 40]);
        Department::create(['id' => 5, 'name' => 'Department 5', 'number' => 50]);
        Department::create(['id' => 6, 'name' => 'Department 6', 'number' => 60]);
        Department::create(['id' => 7, 'name' => 'Department 7', 'number' => 70]);
        Department::create(['id' => 8, 'name' => 'Department 8', 'number' => 80]);
        Department::create(['id' => 9, 'name' => 'Department 9', 'number' => 90]);
        Department::create(['id' => 10, 'name' => 'Department 10', 'number' => 100]);
    }
}