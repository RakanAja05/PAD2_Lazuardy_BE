<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subjects')->insertOrIgnore([
            [
                'id' => SeedIds::SUBJECT_MATH_ID,
                'class_id' => SeedIds::CLASS_7_ID,
                'name' => 'Matematika',
                'icon_image_path' => 'icons/subjects/math.png',
            ],
            [
                'id' => SeedIds::SUBJECT_ENGLISH_ID,
                'class_id' => SeedIds::CLASS_7_ID,
                'name' => 'Bahasa Inggris',
                'icon_image_path' => 'icons/subjects/english.png',
            ],
        ]);
    }
}
