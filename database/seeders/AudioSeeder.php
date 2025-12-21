<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AudioSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();
        $data = [];

        for ($i = 0; $i < 200; $i++) {
            $data[] = [
                'filename' => $faker->unique()->uuid . '.mp3',

                // Randomly 0 or 1
                'approved' => $faker->numberBetween(0, 1),

                // Fixed to match your session defaults
                'type' => 'media',
                'destination' => 'None',

                // Limits and constraints
                'owner' => substr($faker->userName, 0, 20),
                'title' => substr($faker->words(3, true), 0, 200),
                'artist' => $faker->name,
                'genre' => $faker->word,
                'year' => $faker->year(),
                'description' => 'Test Data',
                'duration' => $faker->randomFloat(2, 60, 300),

                // Table specific column names
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($data) >= 50) {
                DB::table('audios')->insert($data);
                $data = [];
            }
        }

        DB::table('audios')->insert($data);
    }
}