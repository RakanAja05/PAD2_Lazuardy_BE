<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Services\Dashboards\GuestDashboardService;
use Illuminate\Http\Request;

class GuestDashboardController extends Controller
{
    public function __construct(private readonly GuestDashboardService $guestDashboardService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/guest",
     *     tags={"Dashboards"},
     *     summary="Guest dashboard",
     *     @OA\Response(response=200, description="Guest dashboard data", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index(Request $request)
    {
        $result = $this->guestDashboardService->index($request);

        return $this->respond($result);
    }
}
