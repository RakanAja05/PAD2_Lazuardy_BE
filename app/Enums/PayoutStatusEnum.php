<?php

namespace App\Enums;

enum PayoutStatusEnum: string
{
    case REQUESTED = 'requested';
    case PENDING   = 'pending';
    case SUCCESS   = 'success';
    case REJECTED  = 'rejected';
    case FAILED    = 'failed';

    public function displayName(): string
    {
        return match($this) {
            self::REQUESTED => 'Diajukan',
            self::PENDING   => 'Diproses',
            self::SUCCESS   => 'Berhasil',
            self::REJECTED  => 'Ditolak',
            self::FAILED    => 'Gagal',
        };
    }

    public static function list(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function displayList(): array
    {
        return array_map(fn($case) => $case->displayName(), self::cases());
    }
}
