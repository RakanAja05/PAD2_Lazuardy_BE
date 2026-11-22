<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('reviews')->insertOrIgnore([
            // Tutor 4 (Depok, offline only) - should be included (same city), highest rating
            [
                'id' => 1,
                'tutor_id' => SeedIds::TUTOR_USER_4_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 5.00,
                'comment' => 'Tutor sangat membantu dan komunikatif.',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
            [
                'id' => 2,
                'tutor_id' => SeedIds::TUTOR_USER_4_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 4.90,
                'comment' => 'Pembelajaran terstruktur dan jelas.',
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],

            // Tutor 1 (Bandung, online) - should be included (out-of-city but online)
            [
                'id' => 3,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 4.80,
                'comment' => 'Penjelasan jelas dan mudah dipahami.',
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ],
            [
                'id' => 4,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 4.80,
                'comment' => 'Responsif dan sabar.',
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now->copy()->subDays(4),
            ],
            [
                'id' => 5,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 4.70,
                'comment' => 'Materi sesuai kebutuhan.',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ],

            // Tutor 3 (Jakarta, online) - should be included (out-of-city but online)
            [
                'id' => 6,
                'tutor_id' => SeedIds::TUTOR_USER_3_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 4.50,
                'comment' => 'Tutor ramah dan mudah diikuti.',
                'created_at' => $now->copy()->subDays(6),
                'updated_at' => $now->copy()->subDays(6),
            ],

            // Tutor 2 (Bandung, offline only) - should be excluded (out-of-city & not online)
            [
                'id' => 7,
                'tutor_id' => SeedIds::TUTOR_USER_2_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 5.00,
                'comment' => 'Bagus, tapi hanya offline.',
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(7),
            ],
        ]);
    }
}
