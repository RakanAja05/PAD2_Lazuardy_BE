<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class XenditService
{
    public function createInvoice(
        string $externalId,
        int $amount,
        string $email
    ): array {
        $response = Http::withBasicAuth(
            config('xendit.secret_key'),
            ''
        )->post(
            'https://api.xendit.co/v2/invoices',
            [
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $email,
                'currency' => 'IDR',
            ]
        )->throw();

        return $response->json();
    }
}
