<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = "pending";
    case PAID = "paid";
    case FAILED = "failed";
    case EXPIRED = "expired";
    
    
    public function displayName() : string 
    {
        return match($this) 
        {
            self::PENDING => "Menunggu pembayaran",
            self::PAID => 'Pembayaran berhasil',
            self::FAILED => 'Pembayaran gagal',
            self::EXPIRED => 'Pembayaran kadaluarsa',
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
