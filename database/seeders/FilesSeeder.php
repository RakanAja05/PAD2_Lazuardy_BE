<?php

namespace Database\Seeders;

use App\Enums\FileTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('files')->insertOrIgnore([
            [
                'id' => SeedIds::FILE_TUTOR_1_ID,
                'user_id' => SeedIds::TUTOR_USER_ID,
                'name' => 'ID Card Tutor 1',
                'type' => FileTypeEnum::KTP->value,
                'path' => 'files/id-card-tutor-1-demo.jpg',
                'status' => 'uploaded',
            ],
            [
                'id' => SeedIds::FILE_TUTOR_2_ID,
                'user_id' => SeedIds::TUTOR_USER_2_ID,
                'name' => 'CV Tutor 2',
                'type' => FileTypeEnum::CV->value,
                'path' => 'files/cv-tutor-2-demo.pdf',
                'status' => 'uploaded',
            ],
            [
                'id' => SeedIds::FILE_TUTOR_3_ID,
                'user_id' => SeedIds::TUTOR_USER_3_ID,
                'name' => 'Certificate Tutor 3',
                'type' => FileTypeEnum::CERTIFICATE->value,
                'path' => 'files/certificate-tutor-3-demo.jpg',
                'status' => 'uploaded',
            ],
            [
                'id' => SeedIds::FILE_TUTOR_4_ID,
                'user_id' => SeedIds::TUTOR_USER_4_ID,
                'name' => 'Diploma Tutor 4',
                'type' => FileTypeEnum::IJAZAH->value,
                'path' => 'files/diploma-tutor-4-demo.jpg',
                'status' => 'uploaded',
            ],
            [
                'id' => SeedIds::FILE_TUTOR_5_ID,
                'user_id' => SeedIds::TUTOR_USER_5_ID,
                'name' => 'Portfolio Tutor 5',
                'type' => FileTypeEnum::PORTOFOLIO->value,
                'path' => 'files/portfolio-tutor-5-demo.pdf',
                'status' => 'uploaded',
            ],
        ]);
    }
}
