<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable =
    [
        'order_id',
        'external_id',
        'xendit_id',
        'amount',
        'payment_method',
        'payment_channel',
        'status',
        'checkout_url',
        'paid_at',
        'payload_raw',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatusEnum::class,
            'paid_at' => 'datetime',
            'payload_raw' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
