<?php

namespace App\Http\Controllers\Tutors;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTutorApplicationRequest;
use App\Services\Tutors\TutorApplicationService;
use Illuminate\Http\Request;

class TutorApplicationController extends Controller
{
    public function __construct(private readonly TutorApplicationService $tutorApplicationService)
    {
    }

    public function index(Request $request)
    {
        $result = $this->tutorApplicationService->index($request);

        return $this->respond($result);
    }

    /**
     * @OA\Post(
     *     path="/api/tutor/apply",
     *     tags={"Tutors"},
     *     summary="Submit tutor application",
     *     security={{"bearerAuth":{}}},
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"experience","organization"},
     *             @OA\Property(property="experience", type="string"),
     *             @OA\Property(property="organization", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="cv", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="path_url", type="string")
     *             )),
    *             @OA\Property(property="id_card", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="path_url", type="string")
     *             )),
    *             @OA\Property(property="diploma", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="path_url", type="string")
     *             )),
     *             @OA\Property(property="certificate", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="path_url", type="string")
     *             )),
    *             @OA\Property(property="portfolio", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="path_url", type="string")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Application submitted", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function store(StoreTutorApplicationRequest $request)
    {
        $result = $this->tutorApplicationService->store($request);

        return $this->respond($result);
    }
}
