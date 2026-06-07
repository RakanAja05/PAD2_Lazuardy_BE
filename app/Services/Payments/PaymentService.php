<?php

namespace App\Services\Payments;

use App\Models\Payment;

/**
 * @OA\Schema(
 * title="PaymentService",
 * description="Formatted payment data structure",
 * @OA\Property(property="payment_id", type="integer", example=104),
 * @OA\Property(property="amount", type="number", format="float", example=150000),
 * @OA\Property(property="payment_method", type="string", example="bank_transfer"),
 * @OA\Property(property="payment_status", type="string", example="paid"),
 * @OA\Property(property="date_created", type="string", format="date", example="2026-06-07"),
 * @OA\Property(property="time_created", type="string", example="14:30:00")
 * )
 */
class PaymentService
{
    public function getData(Payment $query)
    {
        $data = [
            'payment_id' => $query->id,
            'amount' => $query->amount,
            'payment_method' => $query->payment_method,
            'payment_status' => $query->status,
            'date_created' => $query->created_at->format('Y-m-d'),
            'time_created' => $query->created_at->format('H:i:s'),
        ];

        return $data;
    }
}
