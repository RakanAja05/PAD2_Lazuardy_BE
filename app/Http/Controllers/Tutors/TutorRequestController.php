<?php

namespace App\Http\Controllers\Tutors;

use App\Http\Controllers\Controller;
use App\Services\Tutors\TutorRequestService;
use Illuminate\Http\Request;

class TutorRequestController extends Controller
{
    public function __construct(private readonly TutorRequestService $service)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/tutor/requests",
     *     tags={"Tutors"},
     *     summary="List pending tutor booking requests",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of requests", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function index(Request $request)
    {
        $result = $this->service->index($request);

        return $this->respond($result);
    }


    /**
     * @OA\Patch(
     *     path="/api/tutor/request/{id}/approve",
     *     tags={"Tutors"},
     *     summary="Approve a booking request",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Request approved", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/responses/NotFoundError"))
     * )
     */
    public function approve(Request $request, int $id)
    {
        $result = $this->service->approve($request, $id);

        return $this->respond($result);
    }


    /**
     * @OA\Patch(
     *     path="/api/tutor/request/{id}/reject",
     *     tags={"Tutors"},
     *     summary="Reject a booking request",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=false, @OA\JsonContent(@OA\Property(property="reason", type="string"))),
     *     @OA\Response(response=200, description="Request rejected", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/responses/NotFoundError"))
     * )
     */
    public function reject(Request $request, int $id)
    {
        $result = $this->service->reject($request, $id);

        return $this->respond($result);
    }
}
