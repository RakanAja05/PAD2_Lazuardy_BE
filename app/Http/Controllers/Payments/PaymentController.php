<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\Payments\PaymentControllerService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentControllerService $paymentControllerService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/package/order/{id}",
     *     tags={"Payments"},
     *     summary="Get package info for ordering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Package detail", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function showPaymentPackage(Package $id)
    {
        $result = $this->paymentControllerService->showPaymentPackage($id);

        return $this->respond($result);
    }

    /**
     * @OA\Post(
     *     path="/api/package/order",
     *     tags={"Payments"},
     *     summary="Create order",
     *     security={{"bearerAuth":{}}},
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
    *             required={"package_id","payment_method"},
     *             @OA\Property(property="package_id", type="integer"),
    *             @OA\Property(property="payment_method", type="string", enum={"mandiri","bni","bri","bpr","bpd","qris"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order created", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function storeOrderPackage(Request $request)
    {
        $result = $this->paymentControllerService->storeOrderPackage($request);

        return $this->respond($result);
    }

    /**
     * @OA\Get(
     *     path="/api/payment/history",
     *     tags={"Payments"},
     *     summary="Payment history",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="History", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function showHistory(Request $request)
    {
        $result = $this->paymentControllerService->showHistory($request);

        return $this->respond($result);
    }

    /**
     * @OA\Get(
     *     path="/api/payment/history/detail",
     *     tags={"Payments"},
     *     summary="Payment detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="payment_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function showDetail(Request $request)
    {
        $result = $this->paymentControllerService->showDetail($request);

        return $this->respond($result);
    }
}
