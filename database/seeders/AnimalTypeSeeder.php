<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AnimalType;
class AnimalTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnimalType::create(['id' => 1, 'type' => 'Cat']);
        AnimalType::create(['id' => 2, 'type' => 'Dog']);
        AnimalType::create(['id' => 3, 'type' => 'Bird']);
        AnimalType::create(['id' => 4, 'type' => 'Horse']);
        AnimalType::create(['id' => 5, 'type' => 'Donkey']);
    }
}