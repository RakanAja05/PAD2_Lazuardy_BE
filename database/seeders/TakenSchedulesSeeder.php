<?php

namespace Database\Seeders;

use App\Enums\TakenScheduleStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TakenSchedulesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taken_schedules')->insertOrIgnore([
            [
                'id' => SeedIds::TAKEN_SCHEDULE_ID,
                'student_id' => SeedIds::STUDENT_USER_ID,
                'schedule_tutor_id' => SeedIds::SCHEDULE_TUTOR_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'date' => now()->addDay(),
                'address' => 'Jl. Jadwal No. 5',
                'status' => TakenScheduleStatusEnum::ACTIVE->value,
            ],
        ]);
    }
}
