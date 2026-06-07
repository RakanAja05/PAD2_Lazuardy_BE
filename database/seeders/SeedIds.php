<?php

namespace Database\Seeders;

final class SeedIds
{
    public const ADMIN_USER_ID = 1;
    public const TUTOR_USER_ID = 2;
    public const STUDENT_USER_ID = 3;
    public const PARENT_USER_ID = 4;

    public const TUTOR_USER_2_ID = 5;
    public const TUTOR_USER_3_ID = 6;
    public const TUTOR_USER_4_ID = 7;
    public const TUTOR_USER_5_ID = 8;

    public const CLASS_1_ID = 1;
    public const CLASS_2_ID = 2;
    public const CLASS_3_ID = 3;
    public const CLASS_4_ID = 4;
    public const CLASS_5_ID = 5;
    public const CLASS_6_ID = 6;
    public const CLASS_7_ID = 7;
    public const CLASS_8_ID = 8;
    public const CLASS_9_ID = 9;
    public const CLASS_10_ID = 10;
    public const CLASS_11_ID = 11;
    public const CLASS_12_ID = 12;

    public const SCHEDULE_TUTOR_ID = 1;
    public const SCHEDULE_TUTOR_2_ID = 2;
    public const SCHEDULE_TUTOR_3_ID = 3;
    public const SCHEDULE_TUTOR_4_ID = 4;
    public const SCHEDULE_TUTOR_5_ID = 5;
    public const TAKEN_SCHEDULE_ID = 1;

    public const PACKAGE_BASIC_ID = 1;
    public const PACKAGE_GIAT_ID = 2;
    public const ORDER_ID = 1;
    public const ORDER_ITEM_ID = 1;
    public const PAYMENT_ID = 1;

    public const FILE_TUTOR_1_ID = 1;
    public const FILE_TUTOR_2_ID = 2;
    public const FILE_TUTOR_3_ID = 3;
    public const FILE_TUTOR_4_ID = 4;
    public const FILE_TUTOR_5_ID = 5;

    // Notification UUIDs (Laravel notifications use UUID primary keys)
    public const NOTIFICATION_STUDENT_DEMO_ID = '11111111-1111-1111-1111-111111111111';
    public const NOTIFICATION_TUTOR_DEMO_ID = '22222222-2222-2222-2222-222222222222';
    public const NOTIFICATION_ADMIN_DEMO_ID = '33333333-3333-3333-3333-333333333333';

    private function __construct()
    {
    }
}
