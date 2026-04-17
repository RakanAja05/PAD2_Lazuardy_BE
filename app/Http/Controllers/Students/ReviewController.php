<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Services\Students\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/student/review",
     *     tags={"Students"},
     *     summary="List tutors to review",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Review list", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index(Request $request)
    {
        $result = $this->reviewService->index($request);

        return response()->json($result->payload, $result->code);
    }

    /**
     * @OA\Post(
     *     path="/api/student/review",
     *     tags={"Students"},
     *     summary="Create review",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tutor_id","rate"},
     *             @OA\Property(property="tutor_id", type="integer"),
     *             @OA\Property(property="rate", type="number", format="float"),
     *             @OA\Property(property="comment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Review saved", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    /**
     * @OA\Patch(
     *     path="/api/student/review",
     *     tags={"Students"},
     *     summary="Update review",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tutor_id","rate"},
     *             @OA\Property(property="tutor_id", type="integer"),
     *             @OA\Property(property="rate", type="number", format="float"),
     *             @OA\Property(property="comment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Review saved", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function storeOrUpdate(Request $request)
    {
        $result = $this->reviewService->storeOrUpdate($request);

        return response()->json($result->payload, $result->code);
    }

    /**
     * @OA\Get(
     *     path="/api/student/review/{tutorId}",
     *     tags={"Students"},
     *     summary="Get review detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="tutorId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Review detail", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function show(Request $request, int $tutorId)
    {
        $result = $this->reviewService->show($request, $tutorId);

        return response()->json($result->payload, $result->code);
    }

    public function update(Request $request, int $id)
    {
        $result = $this->reviewService->update($request, $id);

        return response()->json($result->payload, $result->code);
    }
}
