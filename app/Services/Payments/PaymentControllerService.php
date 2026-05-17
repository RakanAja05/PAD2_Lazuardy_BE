<?php

namespace App\Services\Payments;

use App\DTOs\ResponseDTO;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Throwable;

class PaymentControllerService
{
    public function __construct(private readonly XenditService $xenditService)
    {
    }

    public function showPaymentPackage(Package $package): ResponseDTO
    {
        return new ResponseDTO(
            'success',
            'Berhasil mengambil detail paket',
            $package,
            null,
            200
        );
    }

    public function storeOrderPackage(Request $request): ResponseDTO
    {
        $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'payment_method' => ['required', new Enum(PaymentMethodEnum::class)],
        ]);

        $user = $request->user();
        $package = Package::findOrFail($request->package_id);
        $amount = (int) $package->price;
        $externalId = 'INV-' . Str::uuid()->toString();

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                'total_amount' => $amount,
                'status' => OrderStatusEnum::PENDING->value,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'qty' => 1,
                'price' => $amount,
                'subtotal' => $amount,
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => PaymentStatusEnum::PENDING->value,
                'external_id' => $externalId,
            ]);

            $invoice = $this->xenditService->createInvoice(
                $externalId,
                $amount,
                $user->email
            );

            $payment->update([
                'xendit_id' => $invoice['id'] ?? null,
                'checkout_url' => $invoice['invoice_url'] ?? null,
                'payload_raw' => $invoice,
            ]);

            DB::commit();

            return new ResponseDTO(
                'success',
                'Berhasil membuat order',
                [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'external_id' => $externalId,
                    'checkout_url' => $payment->checkout_url,
                ],
                null,
                200
            );
        } catch (Throwable $e) {
            DB::rollBack();

            return new ResponseDTO(
                'error',
                'Gagal membuat order: ' . $e->getMessage(),
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function showHistory(Request $request): ResponseDTO
    {
        try {
            $user = $request->user();

            $payments = Payment::whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('order.packages')
                ->orderBy('created_at', 'desc')
                ->get();

            $historyData = [];

            foreach ($payments as $payment) {
                $order = $payment->order;
                $package = $order?->packages?->first();

                if ($package) {
                    $historyData[] = [
                        'payment_id' => $payment->id,
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'session' => $package->session,
                        'amount' => $payment->amount,
                        'payment_method' => $payment->payment_method,
                        'payment_status' => $payment->status,
                        'date_created' => $payment->created_at->format('Y-m-d'),
                        'time_created' => $payment->created_at->format('H:i:s'),
                    ];
                }
            }

            return new ResponseDTO(
                'success',
                'Berhasil mengambil riwayat transaksi',
                $historyData,
                null,
                200
            );
        } catch (Throwable $e) {
            return new ResponseDTO(
                'error',
                'Terjadi kesalahan saat memproses riwayat transaksi.',
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function showDetail(Request $request): ResponseDTO
    {
        $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
        ]);

        $payment = Payment::with(['order.packages'])
            ->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->where('id', $request->payment_id)
            ->firstOrFail();

        $package = $payment->order?->packages?->first();
        if (!$package) {
            return new ResponseDTO(
                'error',
                'Paket tidak ditemukan pada order',
                null,
                [
                    'package' => 'not_found',
                ],
                404
            );
        }
        $data = [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'session' => $package->session,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'payment_status' => $payment->status,
            'date_created' => $payment->created_at->format('Y-m-d'),
            'time_created' => $payment->created_at->format('H:i:s'),
        ];

        return new ResponseDTO(
            'success',
            'Berhasil mengambil detail pembayaran',
            $data,
            null,
            200
        );
    }
}
