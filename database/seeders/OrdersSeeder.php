<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('orders')->insertOrIgnore([
            [
                'id' => SeedIds::ORDER_ID,
                'order_number' => 'ORD-000001',
                'user_id' => SeedIds::STUDENT_USER_ID,
                'total_amount' => 200000,
                'status' => OrderStatusEnum::PENDING->value,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
