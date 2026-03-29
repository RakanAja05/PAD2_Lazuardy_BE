<?php

namespace Database\Seeders;

use App\Enums\OtpIdentifierEnum;
use App\Enums\OtpTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OtpsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('otps')->insertOrIgnore([
            [
                'id' => 1,
                'identifier' => 'student@example.test',
                'identifier_type' => OtpIdentifierEnum::EMAIL->value,
                'code' => '123456',
                'verification_type' => OtpTypeEnum::REGISTER->value,
                'attempts' => 0,
                'is_used' => false,
                'expired_at' => $now->copy()->addMinutes(10),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
