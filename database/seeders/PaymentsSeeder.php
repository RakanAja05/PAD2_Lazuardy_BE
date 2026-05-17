<?php

namespace Database\Seeders;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('payments')->insertOrIgnore([
            [
                'id' => SeedIds::PAYMENT_ID,
                'order_id' => SeedIds::ORDER_ID,
                'external_id' => 'ext-demo-1',
                'xendit_id' => 'xdt-demo-1',
                'payment_method' => 'mandiri',
                'payment_channel' => 'invoice',
                'amount' => 200000,
                'status' => PaymentStatusEnum::PENDING->value,
                'checkout_url' => 'https://example.test/checkout/ORD-000001',
                'paid_at' => null,
                'payload_raw' => json_encode([
                    'note' => 'Seed payment payload',
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
