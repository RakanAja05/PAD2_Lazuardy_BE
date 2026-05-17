<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\TutorVerifyService;
use Illuminate\Http\Request;

class TutorVerifyController extends Controller
{
    public function __construct(private readonly TutorVerifyService $tutorVerifyService)
    {
    }

    /**
     * @OA\Get(
    *     path="/api/admin/tutor/verify",
     *     tags={"Admin"},
     *     summary="List tutors pending verification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Pending tutors", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index()
    {
        $result = $this->tutorVerifyService->index();

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/tutor/{userId}/verify/approve",
     *     tags={"Admin"},
     *     summary="Approve tutor verification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Approved", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function approve(int $userId)
    {
        $validated = [
            'user_id' => $userId,
        ];

        $result = $this->tutorVerifyService->approve($validated);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/tutor/{userId}/verify/reject",
     *     tags={"Admin"},
     *     summary="Reject tutor verification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
    *     @OA\RequestBody(
    *         required=false,
    *         @OA\JsonContent(
    *             @OA\Property(property="reason", type="string", nullable=true)
    *         )
    *     ),
     *     @OA\Response(response=200, description="Rejected", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function reject(Request $request, int $userId)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = $userId;

        $result = $this->tutorVerifyService->reject($validated);

        return $this->respond($result);
    }
}
