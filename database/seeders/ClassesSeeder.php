<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('classes')->insertOrIgnore([
            ['id' => SeedIds::CLASS_1_ID, 'name' => 'Kelas 1', 'level' => 'SD'],
            ['id' => SeedIds::CLASS_2_ID, 'name' => 'Kelas 2', 'level' => 'SD'],
            ['id' => SeedIds::CLASS_3_ID, 'name' => 'Kelas 3', 'level' => 'SD'],
            ['id' => SeedIds::CLASS_4_ID, 'name' => 'Kelas 4', 'level' => 'SD'],
            ['id' => SeedIds::CLASS_5_ID, 'name' => 'Kelas 5', 'level' => 'SD'],
            ['id' => SeedIds::CLASS_6_ID, 'name' => 'Kelas 6', 'level' => 'SD'],
            ['id' => SeedIds::CLASS_7_ID, 'name' => 'Kelas 7', 'level' => 'SMP'],
            ['id' => SeedIds::CLASS_8_ID, 'name' => 'Kelas 8', 'level' => 'SMP'],
            ['id' => SeedIds::CLASS_9_ID, 'name' => 'Kelas 9', 'level' => 'SMP'],
            ['id' => SeedIds::CLASS_10_ID, 'name' => 'Kelas 10', 'level' => 'SMA'],
            ['id' => SeedIds::CLASS_11_ID, 'name' => 'Kelas 11', 'level' => 'SMA'],
            ['id' => SeedIds::CLASS_12_ID, 'name' => 'Kelas 12', 'level' => 'SMA'],
        ]);
    }
}
