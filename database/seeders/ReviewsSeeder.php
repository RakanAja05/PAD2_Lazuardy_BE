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
            [
                'id' => 1,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'rate' => 4.75,
                'comment' => 'Penjelasan jelas dan mudah dipahami.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
