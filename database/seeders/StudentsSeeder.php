<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('students')->insertOrIgnore([
            'user_id' => SeedIds::STUDENT_USER_ID,
            'class_id' => SeedIds::CLASS_7_ID,
            'session' => 0,
            'sanction' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
