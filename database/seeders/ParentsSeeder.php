<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('parents')->insertOrIgnore([
            'user_id' => SeedIds::PARENT_USER_ID,
            'student_id' => SeedIds::STUDENT_USER_ID,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
