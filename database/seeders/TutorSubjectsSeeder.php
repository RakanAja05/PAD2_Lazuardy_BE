<?php

namespace Database\Seeders;

use App\Enums\SubjectApplicationEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tutor_subjects')->insertOrIgnore([
            [
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'status' => SubjectApplicationEnum::ACCEPTED->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_ID,
                'subject_id' => SeedIds::SUBJECT_ENGLISH_ID,
                'status' => SubjectApplicationEnum::VERIFY->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_2_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'status' => SubjectApplicationEnum::ACCEPTED->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_2_ID,
                'subject_id' => SeedIds::SUBJECT_ENGLISH_ID,
                'status' => SubjectApplicationEnum::ACCEPTED->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_3_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'status' => SubjectApplicationEnum::VERIFY->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_3_ID,
                'subject_id' => SeedIds::SUBJECT_ENGLISH_ID,
                'status' => SubjectApplicationEnum::ACCEPTED->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_4_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'status' => SubjectApplicationEnum::REJECTED->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_4_ID,
                'subject_id' => SeedIds::SUBJECT_ENGLISH_ID,
                'status' => SubjectApplicationEnum::VERIFY->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_5_ID,
                'subject_id' => SeedIds::SUBJECT_MATH_ID,
                'status' => SubjectApplicationEnum::ACCEPTED->value,
            ],
            [
                'tutor_id' => SeedIds::TUTOR_USER_5_ID,
                'subject_id' => SeedIds::SUBJECT_ENGLISH_ID,
                'status' => SubjectApplicationEnum::VERIFY->value,
            ],
        ]);
    }
}
