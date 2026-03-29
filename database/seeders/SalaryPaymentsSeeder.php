<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaryPaymentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('salary_payments')->insertOrIgnore([
            [
                'id' => 1,
                'user_id' => SeedIds::TUTOR_USER_ID,
                'amount' => 50000,
                'invoice_url' => null,
                'payment_method' => 'manual',
                'note' => 'Gaji demo hasil seeding.',
                'paid_at' => null,
                'email_sent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
