<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminPayoutService;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function __construct(private readonly AdminPayoutService $adminPayoutService) {}

    /**
     * @OA\Get(
     *     path="/api/admin/payout",
     *     tags={"Admin"},
     *     summary="Daftar pengajuan payout tutor",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Daftar payout", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index()
    {
        return $this->respond($this->adminPayoutService->index());
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/payout/{id}/approve",
     *     tags={"Admin"},
     *     summary="Setujui pengajuan payout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Disetujui", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function approve(Request $request, int $id)
    {
        return $this->respond($this->adminPayoutService->approve($request, $id));
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/payout/{id}/reject",
     *     tags={"Admin"},
     *     summary="Tolak pengajuan payout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Ditolak", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function reject(Request $request, int $id)
    {
        return $this->respond($this->adminPayoutService->reject($request, $id));
    }
}
