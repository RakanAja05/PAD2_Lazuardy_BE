<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $subjectsByLevel = [
            'SD' => [
                SeedIds::CLASS_1_ID, SeedIds::CLASS_2_ID, SeedIds::CLASS_3_ID,
                SeedIds::CLASS_4_ID, SeedIds::CLASS_5_ID, SeedIds::CLASS_6_ID,
            ],
            'SMP' => [
                SeedIds::CLASS_7_ID, SeedIds::CLASS_8_ID, SeedIds::CLASS_9_ID,
            ],
            'SMA' => [
                SeedIds::CLASS_10_ID, SeedIds::CLASS_11_ID, SeedIds::CLASS_12_ID,
            ],
        ];

        $subjectNames = [
            'SD' => ['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'PKN', 'Bahasa Inggris', 'Agama'],
            'SMP' => ['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS', 'PKN', 'Bahasa Inggris', 'Agama', 'Seni Budaya', 'Prakarya'],
            'SMA' => ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi', 'Ekonomi', 'Sejarah', 'Geografi', 'Sosiologi', 'PKN', 'Agama'],
        ];

        $rows = [];
        foreach ($subjectsByLevel as $level => $classIds) {
            foreach ($classIds as $classId) {
                foreach ($subjectNames[$level] as $name) {
                    $rows[] = [
                        'class_id' => $classId,
                        'name' => $name,
                        'icon_image_path' => null,
                    ];
                }
            }
        }

        DB::table('subjects')->insertOrIgnore($rows);
    }
}
