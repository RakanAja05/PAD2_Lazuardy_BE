<?php

namespace App\Services\Students;

use App\DTOs\ResponseDTO;
use App\Enums\TakenScheduleStatusEnum;
use App\Models\TakenSchedule;
use App\Services\Schedules\ScheduleService;
use Illuminate\Http\Request;

class StudentRequestService
{
    public function __construct(private readonly ScheduleService $scheduleService) {}

    public function index(Request $request): ResponseDTO
    {
        return $this->scheduleService->indexStudent($request);
    }

    public function store(Request $request): ResponseDTO
    {
        return $this->scheduleService->storeStudent($request);
    }

    public function cancel(Request $request, int $id): ResponseDTO
    {
        $user = $request->user();

        $schedule = TakenSchedule::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::PENDING->value)
            ->first();

        if (!$schedule) {
            return new ResponseDTO(
                'error',
                'Pengajuan tidak ditemukan atau tidak bisa dibatalkan',
                null,
                ['id' => 'not_found'],
                404
            );
        }

        $schedule->status = TakenScheduleStatusEnum::CANCELLED->value;
        $schedule->save();

        return new ResponseDTO(
            'success',
            'Pengajuan berhasil dibatalkan',
            ['schedule' => [
                'id' => $schedule->id,
                'status' => $schedule->status,
            ]],
            null,
            200
        );
    }
}
