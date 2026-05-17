<?php

namespace App\Enums;

enum ScheduleStatusEnum: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';

    public static function list(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function displayList(): array
    {
        return [
            self::PENDING->value => 'Menunggu',
            self::ACTIVE->value => 'Aktif',
            self::REJECTED->value => 'Ditolak',
            self::CANCELLED->value => 'Dibatalkan',
            self::EXPIRED->value => 'Terlewat',
            self::COMPLETED->value => 'Selesai',
        ];
    }

    public function display(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::ACTIVE => 'Aktif',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Terlewat',
            self::COMPLETED => 'Selesai',
        };
    }
}
