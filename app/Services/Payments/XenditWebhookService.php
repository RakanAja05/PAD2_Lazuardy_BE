<?php

namespace App\Services\Payments;

use App\DTOs\ResponseDTO;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class XenditWebhookService
{
    public function handle(Request $request): ResponseDTO
    {

        $callbackToken = (string) $request->header('x-callback-token');
        $expectedToken = (string) config('xendit.callback_token');

        \Log::info('Xendit Webhook Token Debug', [
    'received'  => $callbackToken,
    'expected'  => $expectedToken,
    'match'     => $callbackToken === $expectedToken,
]);

        if ($expectedToken !== '' && !hash_equals($expectedToken, $callbackToken)) {
            return new ResponseDTO(
                'error',
                'Callback token tidak valid',
                null,
                [
                    'token' => 'invalid',
                ],
                401
            );
        }

        $payload = $request->all();
        $externalId = $payload['external_id'] ?? null;

        if (!$externalId) {
            return new ResponseDTO(
                'error',
                'external_id tidak ditemukan',
                null,
                [
                    'external_id' => 'missing',
                ],
                400
            );
        }

        $payment = Payment::with('order')
            ->where('external_id', $externalId)
            ->first();

        if (!$payment) {
            return new ResponseDTO(
                'error',
                'Payment tidak ditemukan',
                null,
                [
                    'external_id' => 'not_found',
                ],
                404
            );
        }

        $status = strtoupper((string) ($payload['status'] ?? ''));
        $paymentUpdates = [
            'payload_raw' => $payload,
        ];
        $orderStatus = null;

        if ($status === 'PAID') {
            $paymentUpdates['status'] = PaymentStatusEnum::PAID->value;
            $paymentUpdates['paid_at'] = isset($payload['paid_at'])
                ? Carbon::parse($payload['paid_at'])
                : now();
            $orderStatus = OrderStatusEnum::PAID->value;
        } elseif ($status === 'EXPIRED') {
            $paymentUpdates['status'] = PaymentStatusEnum::EXPIRED->value;
        } elseif ($status === 'FAILED') {
            $paymentUpdates['status'] = PaymentStatusEnum::FAILED->value;
        }

        DB::transaction(function () use ($payment, $paymentUpdates, $orderStatus) {
            $payment->update($paymentUpdates);

            if ($orderStatus && $payment->order) {
                $payment->order->update([
                    'status' => $orderStatus,
                ]);
            }
        });

        return new ResponseDTO(
            'success',
            'Webhook Xendit diterima',
            [
                'external_id' => $externalId,
                'status' => $payment->status instanceof PaymentStatusEnum
                    ? $payment->status->value
                    : $payment->status,
            ],
            null,
            200
        );
    }
}
