<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('courses')->insert([
            [
                'title' => 'Introduction to Laravel',
                'description' => 'Basic concepts of Laravel framework.',
                'join_password' => Str::random(10),
                'teacher_id' => 1, // pastikan user dengan ID 1 adalah guru
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Advanced PHP',
                'description' => 'In-depth PHP techniques and features.',
                'join_password' => Str::random(10),
                'teacher_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
