<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('notifications')->insertOrIgnore([
            [
                'id' => 1,
                'type' => 'Database\\Seeders\\DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => SeedIds::STUDENT_USER_ID,
                'data' => json_encode([
                    'title' => 'Notifikasi Demo',
                    'message' => 'Ini notifikasi hasil seeding.',
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
