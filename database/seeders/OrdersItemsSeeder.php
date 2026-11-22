<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersItemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('orders_items')->insertOrIgnore([
            [
                'id' => SeedIds::ORDER_ITEM_ID,
                'order_id' => SeedIds::ORDER_ID,
                'package_id' => SeedIds::PACKAGE_BASIC_ID,
                'qty' => 1,
                'price' => 200000,
                'subtotal' => 200000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
