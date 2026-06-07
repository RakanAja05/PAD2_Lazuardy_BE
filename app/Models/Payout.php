<?php

namespace App\Models;

use App\Enums\PayoutStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    protected $table = 'payouts';

    protected $fillable = [
        'tutor_id',
        'payout_number',
        'xendit_id',
        'amount',
        'bank_code',
        'account_number',
        'account_holder_name',
        'status',
        'failure_code',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'payload_raw',
    ];

    protected $casts = [
        'status'      => PayoutStatusEnum::class,
        'approved_at' => 'datetime',
        'payload_raw' => 'array',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
