<?php

namespace Database\Seeders;

use App\Enums\TutorStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorConfirmsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('tutor_confirms')->insertOrIgnore([
            [
                'id' => 1,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'schedule_tutor_id' => SeedIds::SCHEDULE_TUTOR_ID,
                'reason' => null,
                'address' => 'Jl. Konfirmasi No. 6',
                'status' => TutorStatusEnum::VERIFIED->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
