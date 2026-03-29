<?php

namespace Database\Seeders;

use App\Enums\DayEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleTutorsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schedule_tutors')->insertOrIgnore([
            [
                'id' => SeedIds::SCHEDULE_TUTOR_ID,
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'day' => DayEnum::MONDAY->value,
                'time' => '16:00:00',
            ],
            [
                'id' => SeedIds::SCHEDULE_TUTOR_2_ID,
                'tutor_id' => SeedIds::TUTOR_USER_2_ID,
                'day' => DayEnum::TUESDAY->value,
                'time' => '18:00:00',
            ],
            [
                'id' => SeedIds::SCHEDULE_TUTOR_3_ID,
                'tutor_id' => SeedIds::TUTOR_USER_3_ID,
                'day' => DayEnum::WEDNESDAY->value,
                'time' => '15:00:00',
            ],
            [
                'id' => SeedIds::SCHEDULE_TUTOR_4_ID,
                'tutor_id' => SeedIds::TUTOR_USER_4_ID,
                'day' => DayEnum::THURSDAY->value,
                'time' => '17:30:00',
            ],
            [
                'id' => SeedIds::SCHEDULE_TUTOR_5_ID,
                'tutor_id' => SeedIds::TUTOR_USER_5_ID,
                'day' => DayEnum::FRIDAY->value,
                'time' => '19:00:00',
            ],
        ]);
    }
}
