<?php

namespace App\Services\Admin;

use App\DTOs\ResponseDTO;
use App\Models\SalaryPayment;
use App\Models\User;

class SalaryPaymentHistoryService
{
    public function getPaymentHistory($userId): ResponseDTO
    {
        $user = User::findOrFail($userId);

        $payments = SalaryPayment::where('user_id', $userId)
            ->orderBy('paid_at', 'desc')
            ->get();

        return new ResponseDTO(
            'success',
            'Payment history retrieved successfully',
            $payments,
            null,
            200
        );
    }

    public function sagetPaymentHistory($userId): ResponseDTO
    {
        return $this->getPaymentHistory($userId);
    }
}
