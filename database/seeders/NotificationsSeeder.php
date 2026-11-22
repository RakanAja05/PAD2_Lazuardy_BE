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
                'id' => SeedIds::NOTIFICATION_STUDENT_DEMO_ID,
                'type' => 'Database\\Seeders\\DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => SeedIds::STUDENT_USER_ID,
                'data' => json_encode([
                    'title' => 'Notifikasi Demo (Student)',
                    'message' => 'Ini notifikasi hasil seeding untuk testing endpoint notifications.',
                    'type' => 'demo',
                    'created_at' => $now->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => SeedIds::NOTIFICATION_TUTOR_DEMO_ID,
                'type' => 'Database\\Seeders\\DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => SeedIds::TUTOR_USER_ID,
                'data' => json_encode([
                    'title' => 'Notifikasi Demo (Tutor)',
                    'message' => 'Notifikasi dummy untuk tutor (testing unread/read/delete).',
                    'type' => 'demo',
                    'created_at' => $now->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => SeedIds::NOTIFICATION_ADMIN_DEMO_ID,
                'type' => 'Database\\Seeders\\DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => SeedIds::ADMIN_USER_ID,
                'data' => json_encode([
                    'title' => 'Notifikasi Demo (Admin)',
                    'message' => 'Notifikasi dummy untuk admin (testing list + delete).',
                    'type' => 'demo',
                    'created_at' => $now->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE),
                // Mark one as read so deleteAllRead has data
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
