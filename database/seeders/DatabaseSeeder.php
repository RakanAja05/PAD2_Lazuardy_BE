<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClassesSeeder::class,
            UsersSeeder::class,
            StudentsSeeder::class,
            TutorsSeeder::class,
            ParentsSeeder::class,

            SubjectsSeeder::class,
            TutorSubjectsSeeder::class,

            ScheduleTutorsSeeder::class,
            TakenSchedulesSeeder::class,
            PresencesSeeder::class,
            ReviewsSeeder::class,
            TutorConfirmsSeeder::class,

            PackagesSeeder::class,
            OrdersSeeder::class,
            OrdersItemsSeeder::class,
            PaymentsSeeder::class,

            OtpsSeeder::class,
            NotificationsSeeder::class,
            SalaryPaymentsSeeder::class,
            PersonalAccessTokensSeeder::class,
            CacheSeeder::class,
            CacheLocksSeeder::class,
            FailedJobsSeeder::class,
            JobBatchesSeeder::class,
            JobsSeeder::class,
            PasswordResetTokensSeeder::class,
            SessionsSeeder::class,
        ]);
    }
}
