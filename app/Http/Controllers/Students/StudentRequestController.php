<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Services\Students\StudentRequestService;
use Illuminate\Http\Request;

class StudentRequestController extends Controller
{
    public function __construct(private readonly StudentRequestService $studentRequestService) {}

    /**
     * @OA\Get(
     *     path="/api/student/request",
     *     tags={"Students"},
     *     summary="List pengajuan jadwal student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List pengajuan", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index(Request $request)
    {
        $result = $this->studentRequestService->index($request);
        return $this->respond($result);
    }

    /**
     * @OA\Post(
     *     path="/api/student/request",
     *     tags={"Students"},
     *     summary="Buat pengajuan jadwal ke tutor",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"schedule_id","address"},
     *             @OA\Property(property="schedule_id", type="integer"),
     *             @OA\Property(property="subject_id", type="integer"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="reason", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pengajuan dibuat", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function store(Request $request)
    {
        $result = $this->studentRequestService->store($request);
        return $this->respond($result);
    }

    /**
     * @OA\Delete(
     *     path="/api/student/request/{id}",
     *     tags={"Students"},
     *     summary="Batalkan pengajuan jadwal",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Pengajuan dibatalkan", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function cancel(Request $request, int $id)
    {
        $result = $this->studentRequestService->cancel($request, $id);
        return $this->respond($result);
    }
}
