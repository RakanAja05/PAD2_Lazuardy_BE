<?php

namespace Database\Seeders;

use App\Enums\TutorStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('tutors')->insertOrIgnore([
            [
                'user_id' => SeedIds::TUTOR_USER_ID,
                'education' => json_encode([
                    ['institution' => 'Universitas Contoh', 'major' => 'Pendidikan', 'year' => 2017],
                ], JSON_UNESCAPED_UNICODE),
                'salary' => 50000,
                'description' => 'Tutor demo untuk kebutuhan seeding.',
                'learning_method' => json_encode(['offline', 'online'], JSON_UNESCAPED_UNICODE),
                'bank_code' => 'BCA',
                'account_number' => '1234567890',
                'sanction' => null,
                'status' => TutorStatusEnum::VERIFIED->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => SeedIds::TUTOR_USER_2_ID,
                'education' => json_encode([
                    ['institution' => 'Universitas Contoh', 'major' => 'Matematika', 'year' => 2016],
                ], JSON_UNESCAPED_UNICODE),
                'salary' => 45000,
                'description' => 'Tutor demo 2 untuk kebutuhan seeding.',
                'learning_method' => json_encode(['offline'], JSON_UNESCAPED_UNICODE),
                'bank_code' => 'BRI',
                'account_number' => '2234567890',
                'sanction' => null,
                'status' => TutorStatusEnum::PENDING->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => SeedIds::TUTOR_USER_3_ID,
                'education' => json_encode([
                    ['institution' => 'Universitas Contoh', 'major' => 'Bahasa Inggris', 'year' => 2018],
                ], JSON_UNESCAPED_UNICODE),
                'salary' => 55000,
                'description' => 'Tutor demo 3 untuk kebutuhan seeding.',
                'learning_method' => json_encode(['online'], JSON_UNESCAPED_UNICODE),
                'bank_code' => 'MANDIRI',
                'account_number' => '3234567890',
                'sanction' => null,
                'status' => TutorStatusEnum::VERIFIED->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => SeedIds::TUTOR_USER_4_ID,
                'education' => json_encode([
                    ['institution' => 'Universitas Contoh', 'major' => 'Fisika', 'year' => 2015],
                ], JSON_UNESCAPED_UNICODE),
                'salary' => 48000,
                'description' => 'Tutor demo 4 untuk kebutuhan seeding.',
                'learning_method' => json_encode(['offline', 'online'], JSON_UNESCAPED_UNICODE),
                'bank_code' => 'BCA',
                'account_number' => '4234567890',
                'sanction' => null,
                'status' => TutorStatusEnum::REJECTED->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => SeedIds::TUTOR_USER_5_ID,
                'education' => json_encode([
                    ['institution' => 'Universitas Contoh', 'major' => 'Kimia', 'year' => 2014],
                ], JSON_UNESCAPED_UNICODE),
                'salary' => 52000,
                'description' => 'Tutor demo 5 untuk kebutuhan seeding.',
                'learning_method' => json_encode(['online'], JSON_UNESCAPED_UNICODE),
                'bank_code' => 'BNI',
                'account_number' => '5234567890',
                'sanction' => null,
                'status' => TutorStatusEnum::PENDING->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
