<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PersonalAccessTokensSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('personal_access_tokens')->insertOrIgnore([
            [
                'id' => 1,
                'tokenable_type' => User::class,
                'tokenable_id' => SeedIds::ADMIN_USER_ID,
                'name' => 'seed-token',
                'token' => Str::random(64),
                'abilities' => json_encode(['*']),
                'last_used_at' => null,
                'expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
