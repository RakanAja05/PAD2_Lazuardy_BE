<?php

namespace App\Services\Admin;

use App\DTOs\ResponseDTO;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;

class StudentManagementService
{
    public function index(): ResponseDTO
    {
        $results = Payment::query()
            ->with('order.user.student', 'order.packages')
            ->where('status', PaymentStatusEnum::PENDING->value)
            ->orderBy('created_at', 'asc')
            ->paginate(9);

        return new ResponseDTO(
            'success',
            'Berhasil mengambil daftar pembayaran',
            $results,
            null,
            200
        );
    }

    public function show(Payment $payment): ResponseDTO
    {
        $payment->load('order.user.student', 'order.packages');

        return new ResponseDTO(
            'success',
            'Berhasil mengambil detail pembayaran',
            [
                'detail' => $payment,
            ],
            null,
            200
        );
    }

    public function accept(Payment $payment): ResponseDTO
    {
        return new ResponseDTO(
            'error',
            'Verifikasi manual dinonaktifkan',
            null,
            [
                'feature' => 'disabled',
            ],
            410
        );
    }

    public function reject(Payment $payment): ResponseDTO
    {
        return new ResponseDTO(
            'error',
            'Verifikasi manual dinonaktifkan',
            null,
            [
                'feature' => 'disabled',
            ],
            410
        );
    }
}
