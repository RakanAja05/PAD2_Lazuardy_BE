<?php

namespace App\Services\Tutors;

use App\DTOs\ResponseDTO;
use App\Enums\TakenScheduleStatusEnum;
use App\Models\TakenSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TutorRequestService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user();

        $data = TakenSchedule::query()
            ->where('tutor_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::PENDING->value)
            ->with(['student', 'subject'])
            ->orderByDesc('date')
            ->get()
            ->map(function (TakenSchedule $ts) {
                $dateTime = $ts->date instanceof Carbon
                    ? $ts->date
                    : ($ts->date ? Carbon::parse($ts->date) : null);

                return [
                    'id' => $ts->id,
                    'tutor_user_id' => $ts->tutor_id,
                    'student_user_id' => $ts->student_id,
                    'student_name' => $ts->student?->name,
                    'date' => $dateTime?->toDateString(),
                    'day' => $dateTime?->dayOfWeekIso,
                    'time' => $ts->time,
                    'status' => $ts->status?->value ?? $ts->status,
                ];
            });

        return new ResponseDTO('success', 'Daftar pengajuan jadwal', ['requests' => $data], null, 200);
    }

    public function approve(Request $request, int $id): ResponseDTO
    {
        $user = $request->user();

        $schedule = TakenSchedule::query()
            ->where('id', $id)
            ->where('tutor_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::PENDING->value)
            ->first();

        if (!$schedule) {
            return new ResponseDTO('error', 'Pengajuan tidak ditemukan atau tidak bisa di-approve', null, ['id' => 'not_found'], 404);
        }

        $schedule->status = TakenScheduleStatusEnum::ACTIVE->value;
        $schedule->save();

        return new ResponseDTO('success', 'Pengajuan diterima', ['schedule' => [
            'id' => $schedule->id,
            'status' => $schedule->status,
        ]], null, 200);
    }

    public function reject(Request $request, int $id): ResponseDTO
    {
        $user = $request->user();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule = TakenSchedule::query()
            ->where('id', $id)
            ->where('tutor_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::PENDING->value)
            ->first();

        if (!$schedule) {
            return new ResponseDTO('error', 'Pengajuan tidak ditemukan atau tidak bisa di-reject', null, ['id' => 'not_found'], 404);
        }

        $schedule->status = TakenScheduleStatusEnum::REJECTED->value;
        $schedule->reason = $data['reason'] ?? null;
        $schedule->save();

        return new ResponseDTO('success', 'Pengajuan ditolak', ['schedule' => [
            'id' => $schedule->id,
            'status' => $schedule->status,
        ]], null, 200);
    }
}
