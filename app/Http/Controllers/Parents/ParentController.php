<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Services\Parents\ParentService;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function __construct(private readonly ParentService $parentService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/parent/schedule",
     *     tags={"Parents"},
     *     summary="Parent schedules",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Schedules", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function schedules(Request $request)
    {
        $result = $this->parentService->schedules($request);

        return $this->respond($result);
    }

    /**
     * @OA\Get(
     *     path="/api/parent/learning-results",
     *     tags={"Parents"},
     *     summary="Parent learning results",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Learning results", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function learningResults(Request $request)
    {
        $result = $this->parentService->learningResults($request);

        return $this->respond($result);
    }
}
