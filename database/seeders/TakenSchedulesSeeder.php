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

    $mathId = DB::table('subjects')
        ->where('name', 'Matematika')
        ->where('class_id', SeedIds::CLASS_7_ID)
        ->value('id');

    DB::table('schedules')->insertOrIgnore([
        [
            'id' => SeedIds::TAKEN_SCHEDULE_ID,
            'student_id' => SeedIds::STUDENT_USER_ID,
            'tutor_id' => SeedIds::TUTOR_USER_ID,
            'subject_id' => $mathId,
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
