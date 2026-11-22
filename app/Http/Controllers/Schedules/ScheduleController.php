<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Services\Schedules\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $scheduleService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/student/schedule",
        *     tags={"Schedule"},
     *     summary="Student schedules",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Schedules", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function indexStudent(Request $request)
    {
        $result = $this->scheduleService->indexStudent($request);

        return $this->respond($result);
    }

    /**
     * @OA\Get(
     *     path="/api/student/schedule/history",
        *     tags={"Schedule"},
     *     summary="Student learning history",
        *     description="Riwayat jadwal belajar siswa yang sudah selesai atau tidak aktif.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="History", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function historyStudent(Request $request)
    {
        $result = $this->scheduleService->historyStudent($request);

        return $this->respond($result);
    }

    public function storeStudent(Request $request)
    {
        $result = $this->scheduleService->storeStudent($request);

        return $this->respond($result);
    }

    /**
     * @OA\Get(
     *     path="/api/tutor/schedule",
        *     tags={"Schedule"},
     *     summary="Tutor schedules",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Schedules", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function indexTutor(Request $request)
    {
        $result = $this->scheduleService->indexTutor($request);

        return $this->respond($result);
    }
}
