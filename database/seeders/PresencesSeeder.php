<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresencesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('presences')->insertOrIgnore([
            [
                'id' => 1,
                'taken_schedule_id' => SeedIds::TAKEN_SCHEDULE_ID,
                'tutor_user_id' => SeedIds::TUTOR_USER_ID,
                'student_user_id' => SeedIds::STUDENT_USER_ID,
                'evaluation' => 'Progress baik, lanjut latihan soal.',
                'report' => 90,
                'pbm_image_url' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
