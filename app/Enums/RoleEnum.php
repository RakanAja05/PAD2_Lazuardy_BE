<?php

namespace App\Enums;

enum RoleEnum: string
{
    case DEFAULT = 'undefined';
    case ADMIN = 'admin';
    case PARENT = 'parent';
    case STUDENT = 'student';
    case TUTOR = 'tutor';

    public function displayName() : string
    {
        return match($this)
        {
            self::ADMIN => 'Admin',
            self::PARENT => 'Orang Tua',
            self::TUTOR => 'Mentor',
            self::STUDENT => 'Siswa',
            self::DEFAULT => 'Undefined',
        };
    }
        public static function tryFromDisplayName(string $displayName): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->displayName() === $displayName) {
                return $case;
            }
        }
        return null;
    }
    public static function list() : array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function displayList() : array
    {
        return array_map(fn($case) => $case->displayName(), self::cases());
    }
}
