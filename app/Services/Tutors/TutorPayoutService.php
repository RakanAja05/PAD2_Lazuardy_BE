<?php

namespace App\Services\Tutors;

use App\DTOs\ResponseDTO;
use App\Enums\PayoutStatusEnum;
use App\Models\Payout;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TutorPayoutService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user();

        $payouts = Payout::query()
            ->where('tutor_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'payout_number'  => $p->payout_number,
                'amount'         => $p->amount,
                'status'         => $p->status?->value,
                'status_label'   => $p->status?->displayName(),
                'bank_code'      => $p->bank_code,
                'account_number' => $p->account_number,
                'approved_at'    => $p->approved_at?->toDateTimeString(),
                'rejected_reason'=> $p->rejected_reason,
                'created_at'     => $p->created_at?->toDateTimeString(),
            ]);

        $tutor = Tutor::query()->where('user_id', $user->id)->first();

        return new ResponseDTO('success', 'Riwayat payout', [
            'current_balance' => $tutor?->salary ?? 0,
            'payouts'         => $payouts,
        ], null, 200);
    }

    public function store(Request $request): ResponseDTO
    {
        $user = $request->user();

        $tutor = Tutor::query()->where('user_id', $user->id)->first();

        if (!$tutor || $tutor->salary <= 0) {
            return new ResponseDTO('error', 'Tidak ada saldo yang bisa dicairkan', null, ['balance' => 'empty'], 422);
        }

        $hasPending = Payout::query()
            ->where('tutor_id', $user->id)
            ->whereIn('status', [
                PayoutStatusEnum::REQUESTED->value,
                PayoutStatusEnum::PENDING->value,
            ])
            ->exists();

        if ($hasPending) {
            return new ResponseDTO('error', 'Masih ada pengajuan yang sedang diproses', null, ['status' => 'has_pending'], 422);
        }

        $payout = Payout::query()->create([
            'tutor_id'            => $user->id,
            'payout_number'       => 'PAY-' . strtoupper(Str::random(10)),
            'amount'              => $tutor->salary,
            'bank_code'           => $tutor->bank_code,
            'account_number'      => $tutor->account_number,
            'account_holder_name' => $user->name,
            'status'              => PayoutStatusEnum::REQUESTED->value,
        ]);

        // Kosongkan salary tutor setelah pengajuan dibuat
        $tutor->update(['salary' => 0]);

        return new ResponseDTO('success', 'Pengajuan payout berhasil dibuat', [
            'payout' => [
                'id'            => $payout->id,
                'payout_number' => $payout->payout_number,
                'amount'        => $payout->amount,
                'status'        => $payout->status?->value,
            ],
        ], null, 201);
    }
}
