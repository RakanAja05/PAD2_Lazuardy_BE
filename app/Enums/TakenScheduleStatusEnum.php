<?php

namespace App\Enums;

enum TakenScheduleStatusEnum: string
{
    case PENDING = 'pending';
    case ACTIVE = "active";
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case EXPIRED = "expired";
    case COMPLETED = 'completed';

    public function displayName() : string
    {
        return match($this)
        {
            self::PENDING => 'Menunggu',
            self::ACTIVE => 'Aktif',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Terlewat',
            self::COMPLETED => 'Tuntas',
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
