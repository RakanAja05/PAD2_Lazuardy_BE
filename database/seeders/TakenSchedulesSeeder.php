<?php

namespace Database\Seeders;

use App\Enums\TakenScheduleStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TakenSchedulesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $time = '09:00:00';

        DB::table('schedules')->insertOrIgnore([
            [
                'id' => SeedIds::TAKEN_SCHEDULE_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'date' => $now->copy()->addDay()->setTimeFromTimeString($time),
                'time' => $time,
                'reason' => null,
                'address' => 'Jl. Jadwal No. 5',
                'status' => TakenScheduleStatusEnum::ACTIVE->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
