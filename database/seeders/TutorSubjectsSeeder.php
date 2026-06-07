<?php

namespace Database\Seeders;

use App\Enums\SubjectApplicationEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        // Query subject ID by name + class_id biar tidak hardcode
        $mathId = DB::table('subjects')
            ->where('name', 'Matematika')
            ->where('class_id', SeedIds::CLASS_7_ID)
            ->value('id');

        $englishId = DB::table('subjects')
            ->where('name', 'Bahasa Inggris')
            ->where('class_id', SeedIds::CLASS_7_ID)
            ->value('id');

        DB::table('tutor_subjects')->insertOrIgnore([
            ['tutor_id' => SeedIds::TUTOR_USER_ID,   'subject_id' => $mathId,    'status' => SubjectApplicationEnum::ACCEPTED->value],
            ['tutor_id' => SeedIds::TUTOR_USER_ID,   'subject_id' => $englishId, 'status' => SubjectApplicationEnum::VERIFY->value],
            ['tutor_id' => SeedIds::TUTOR_USER_2_ID, 'subject_id' => $mathId,    'status' => SubjectApplicationEnum::ACCEPTED->value],
            ['tutor_id' => SeedIds::TUTOR_USER_2_ID, 'subject_id' => $englishId, 'status' => SubjectApplicationEnum::ACCEPTED->value],
            ['tutor_id' => SeedIds::TUTOR_USER_3_ID, 'subject_id' => $mathId,    'status' => SubjectApplicationEnum::VERIFY->value],
            ['tutor_id' => SeedIds::TUTOR_USER_3_ID, 'subject_id' => $englishId, 'status' => SubjectApplicationEnum::ACCEPTED->value],
            ['tutor_id' => SeedIds::TUTOR_USER_4_ID, 'subject_id' => $mathId,    'status' => SubjectApplicationEnum::REJECTED->value],
            ['tutor_id' => SeedIds::TUTOR_USER_4_ID, 'subject_id' => $englishId, 'status' => SubjectApplicationEnum::VERIFY->value],
            ['tutor_id' => SeedIds::TUTOR_USER_5_ID, 'subject_id' => $mathId,    'status' => SubjectApplicationEnum::ACCEPTED->value],
            ['tutor_id' => SeedIds::TUTOR_USER_5_ID, 'subject_id' => $englishId, 'status' => SubjectApplicationEnum::VERIFY->value],
        ]);
    }
}
