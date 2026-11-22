<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminStatisticService;
use Illuminate\Http\Request;

class AdminStatisticController extends Controller
{
    public function __construct(private readonly AdminStatisticService $adminStatisticService) {}

    /**
     * @OA\Get(
     *     path="/api/admin/statistics",
     *     tags={"Admin"},
     *     summary="Statistik lengkap admin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         required=false,
     *         description="today | this_week | this_month | this_year | all_time",
     *         @OA\Schema(type="string", default="all_time")
     *     ),
     *     @OA\Response(response=200, description="Statistik", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function summary(Request $request)
    {
        $period = $request->query('period', 'all_time');

        $allowed = ['today', 'this_week', 'this_month', 'this_year', 'all_time'];
        if (!in_array($period, $allowed)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Period tidak valid. Gunakan: ' . implode(', ', $allowed),
            ], 422);
        }

        return $this->respond($this->adminStatisticService->summary($period));
    }
}
