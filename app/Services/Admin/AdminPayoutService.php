<?php

namespace App\Services\Admin;

use App\DTOs\ResponseDTO;
use App\Enums\PayoutStatusEnum;
use App\Models\Payout;
use App\Models\Tutor;
use Illuminate\Http\Request;

class AdminPayoutService
{
    public function index(): ResponseDTO
    {
        $payouts = Payout::query()
            ->with(['tutor'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id'                  => $p->id,
                'payout_number'       => $p->payout_number,
                'tutor_name'          => $p->tutor?->name,
                'amount'              => $p->amount,
                'status'              => $p->status?->value,
                'status_label'        => $p->status?->displayName(),
                'bank_code'           => $p->bank_code,
                'account_number'      => $p->account_number,
                'account_holder_name' => $p->account_holder_name,
                'approved_at'         => $p->approved_at?->toDateTimeString(),
                'rejected_reason'     => $p->rejected_reason,
                'created_at'          => $p->created_at?->toDateTimeString(),
            ]);

        return new ResponseDTO('success', 'Daftar pengajuan payout', ['payouts' => $payouts], null, 200);
    }

    public function approve(Request $request, int $id): ResponseDTO
    {
        $admin = $request->user();

        $payout = Payout::query()
            ->where('id', $id)
            ->where('status', PayoutStatusEnum::REQUESTED->value)
            ->first();

        if (!$payout) {
            return new ResponseDTO('error', 'Pengajuan tidak ditemukan atau sudah diproses', null, ['id' => 'not_found'], 404);
        }

        $payout->update([
            'status'      => PayoutStatusEnum::PENDING->value,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return new ResponseDTO('success', 'Pengajuan payout disetujui', [
            'payout' => [
                'id'     => $payout->id,
                'status' => $payout->status?->value,
            ],
        ], null, 200);
    }

    public function reject(Request $request, int $id): ResponseDTO
    {
        $data = $request->validate([
            'rejected_reason' => ['required', 'string', 'max:255'],
        ]);

        $payout = Payout::query()
            ->where('id', $id)
            ->where('status', PayoutStatusEnum::REQUESTED->value)
            ->first();

        if (!$payout) {
            return new ResponseDTO('error', 'Pengajuan tidak ditemukan atau sudah diproses', null, ['id' => 'not_found'], 404);
        }

        // Kembalikan salary tutor jika ditolak
        Tutor::query()
            ->where('user_id', $payout->tutor_id)
            ->increment('salary', $payout->amount);

        $payout->update([
            'status'          => PayoutStatusEnum::REJECTED->value,
            'rejected_reason' => $data['rejected_reason'],
            'approved_by'     => $request->user()->id,
            'approved_at'     => now(),
        ]);

        return new ResponseDTO('success', 'Pengajuan payout ditolak', [
            'payout' => [
                'id'     => $payout->id,
                'status' => $payout->status?->value,
            ],
        ], null, 200);
    }
}
