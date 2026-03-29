<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackagesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('packages')->insertOrIgnore([
            [
                'id' => SeedIds::PACKAGE_BASIC_ID,
                'name' => 'Paket Basic',
                'session' => 4,
                'price' => 200000,
                'discount' => 0.00,
                'description' => json_encode([
                    '4 sesi belajar',
                    'Tutor berpengalaman',
                ], JSON_UNESCAPED_UNICODE),
                'image_path' => 'packages/basic.png',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
