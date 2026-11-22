<?php

namespace Database\Seeders;

use App\Enums\TakenScheduleStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorConfirmsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('schedules')->insertOrIgnore([
            [
                'id' => SeedIds::TAKEN_SCHEDULE_ID + 1,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'subject_id' => null,
                'date' => $now,
                'time' => null,
                'reason' => null,
                'address' => 'Jl. Konfirmasi No. 6',
                'status' => TakenScheduleStatusEnum::ACTIVE->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
