<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\XenditWebhookService;
use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function __construct(private readonly XenditWebhookService $xenditWebhookService)
    {
    }

    public function handle(Request $request)
    {
        $result = $this->xenditWebhookService->handle($request);

        return $this->respond($result);
    }
}
