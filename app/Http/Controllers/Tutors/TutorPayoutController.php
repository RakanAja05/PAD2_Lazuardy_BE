<?php

namespace App\Http\Controllers\Tutors;

use App\Http\Controllers\Controller;
use App\Services\Tutors\TutorPayoutService;
use Illuminate\Http\Request;

class TutorPayoutController extends Controller
{
    public function __construct(private readonly TutorPayoutService $tutorPayoutService) {}

    /**
     * @OA\Get(
     *     path="/api/tutor/payout",
     *     tags={"Tutors"},
     *     summary="Riwayat payout tutor",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Riwayat payout", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index(Request $request)
    {
        return $this->respond($this->tutorPayoutService->index($request));
    }

    /**
     * @OA\Post(
     *     path="/api/tutor/payout",
     *     tags={"Tutors"},
     *     summary="Ajukan pencairan saldo",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Pengajuan dibuat", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function store(Request $request)
    {
        return $this->respond($this->tutorPayoutService->store($request));
    }
}
