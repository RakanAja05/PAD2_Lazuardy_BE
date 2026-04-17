<?php

namespace App\Services\Admin;

use App\DTOs\ResponseDTO;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StudentManagementService
{
    public function index(): ResponseDTO
    {
        $results = Payment::query()
            ->with('order.user.student', 'order.package')
            ->where('status', PaymentStatusEnum::UPLOADED)
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
        $payment->load('order.user.student', 'order.package');
        $file = Storage::url($payment->proof_image_url);

        return new ResponseDTO(
            'success',
            'Berhasil mengambil detail pembayaran',
            [
                'detail' => $payment,
                'file' => $file,
            ],
            null,
            200
        );
    }

    public function accept(Payment $payment): ResponseDTO
    {
        try {
            $payment->update([
                'status' => PaymentStatusEnum::VALIDATED,
            ]);

            return new ResponseDTO(
                'success',
                'Verifikasi pembayaran diterima',
                [],
                null,
                200
            );
        } catch (Throwable $e) {
            return new ResponseDTO(
                'error',
                'Gagal menerima verifikasi: ' . $e->getMessage(),
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function reject(Payment $payment): ResponseDTO
    {
        try {
            $payment->update([
                'status' => PaymentStatusEnum::REJECTED,
            ]);

            return new ResponseDTO(
                'success',
                'Verifikasi pembayaran ditolak',
                [],
                null,
                200
            );
        } catch (Throwable $e) {
            return new ResponseDTO(
                'error',
                'Gagal menolak verifikasi: ' . $e->getMessage(),
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
