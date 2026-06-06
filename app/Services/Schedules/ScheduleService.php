<?php

namespace App\Services\Schedules;

use App\DTOs\ResponseDTO;
use App\Enums\TakenScheduleStatusEnum;
use App\Models\ScheduleTutor;
use App\Models\TakenSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleService
{
    public function indexStudent(Request $request): ResponseDTO
    {
        $user = $request->user();

        $takenSchedules = $user->takenSchedules()
            ->with(['tutor', 'subject'])
            ->orderByDesc('date')
            ->get();

        $tsData = [];
        foreach ($takenSchedules as $takenSchedule) {
            $dateTime = $takenSchedule->date instanceof Carbon
                ? $takenSchedule->date
                : ($takenSchedule->date ? Carbon::parse($takenSchedule->date) : null);

            $tsData[] = [
                'id' => $takenSchedule->id,
                'tutor_user_id' => $takenSchedule->tutor_id,
                'student_user_id' => $takenSchedule->student_id,
                'tutor_name' => $takenSchedule->tutor?->name,
                'date' => $dateTime?->toDateString(),
                'day' => $dateTime?->dayOfWeekIso,
                'time' => $takenSchedule->time,
                'status' => $takenSchedule->status?->value ?? $takenSchedule->status,
            ];
        }

        return new ResponseDTO(
            'success',
            'Data jadwal berhasil terkirim',
            [
                'schedules' => $tsData,
            ],
            null,
            200
        );
    }

    public function historyStudent(Request $request): ResponseDTO
    {
        $user = $request->user();

        $historySchedules = $user->takenSchedules()
            ->where(function ($query) {
                $query
                    ->whereIn('status', [
                        TakenScheduleStatusEnum::COMPLETED->value,
                        TakenScheduleStatusEnum::CANCELLED->value,
                        TakenScheduleStatusEnum::REJECTED->value,
                        TakenScheduleStatusEnum::EXPIRED->value,
                    ])
                    ->orWhere('date', '<', Carbon::now());
            })
            ->with(['tutor', 'subject'])
            ->orderByDesc('date')
            ->get();

        $historyData = [];
        foreach ($historySchedules as $takenSchedule) {
            $dateTime = $takenSchedule->date instanceof Carbon
                ? $takenSchedule->date
                : ($takenSchedule->date ? Carbon::parse($takenSchedule->date) : null);

            $historyData[] = [
                'id' => $takenSchedule->id,
                'tutor_user_id' => $takenSchedule->tutor_id,
                'student_user_id' => $takenSchedule->student_id,
                'tutor_name' => $takenSchedule->tutor?->name,
                'subject_name' => $takenSchedule->subject?->name,
                'date' => $dateTime?->toDateString(),
                'day' => $dateTime?->dayOfWeekIso,
                'time' => $takenSchedule->time,
                'status' => $takenSchedule->status?->value ?? $takenSchedule->status,
            ];
        }

        return new ResponseDTO(
            'success',
            'Data riwayat belajar berhasil terkirim',
            [
                'history' => $historyData,
            ],
            null,
            200
        );
    }

    public function indexTutor(Request $request): ResponseDTO
    {
        $user = $request->user();

        $data = TakenSchedule::query()
            ->where('tutor_id', $user->id)
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

        return new ResponseDTO(
            'success',
            'Data jadwal berhasil terkirim',
            [
                'schedules' => $data,
            ],
            null,
            200
        );
    }

    public function storeStudent(Request $request): ResponseDTO
    {
        $user = $request->user();

        $data = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:schedule_tutors,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'address' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var ScheduleTutor $scheduleTutor */
        $scheduleTutor = ScheduleTutor::query()->findOrFail($data['schedule_id']);

        $targetIsoDay = $this->dayToIso($scheduleTutor->day);
        if ($targetIsoDay === null) {
            return new ResponseDTO(
                'error',
                'Hari jadwal tutor tidak valid',
                null,
                ['day' => 'invalid'],
                422
            );
        }

        $today = Carbon::today();
        $todayIso = (int) $today->dayOfWeekIso;
        $deltaDays = ($targetIsoDay - $todayIso + 7) % 7;
        if ($deltaDays === 0) {
            $deltaDays = 7;
        }

        $scheduledAt = $today
            ->copy()
            ->addDays($deltaDays)
            ->setTimeFromTimeString((string) $scheduleTutor->time);

        $isTaken = TakenSchedule::query()
            ->where('tutor_id', $scheduleTutor->tutor_id)
            ->whereDate('date', $scheduledAt->toDateString())
            ->where('time', (string) $scheduleTutor->time)
            ->whereIn('status', [
                TakenScheduleStatusEnum::PENDING->value,
                TakenScheduleStatusEnum::ACTIVE->value,
            ])
            ->exists();

        if ($isTaken) {
            return new ResponseDTO(
                'error',
                'Slot sudah diambil, silakan pilih jadwal lain',
                null,
                ['schedule_id' => 'taken'],
                422
            );
        }

        $schedule = TakenSchedule::query()->create([
            'student_id' => $user->id,
            'tutor_id' => $scheduleTutor->tutor_id,
            'subject_id' => $data['subject_id'] ?? null,
            'date' => $scheduledAt,
            'time' => (string) $scheduleTutor->time,
            'reason' => $data['reason'] ?? null,
            'address' => $data['address'],
            'status' => TakenScheduleStatusEnum::PENDING->value,
        ]);

        return new ResponseDTO(
            'success',
            'Pengajuan jadwal berhasil dibuat',
            [
                'schedule' => [
                    'id' => $schedule->id,
                    'date' => $scheduledAt->toDateString(),
                    'time' => (string) $scheduleTutor->time,
                    'status' => $schedule->status?->value ?? $schedule->status,
                    'tutor_user_id' => $scheduleTutor->tutor_id,
                ],
            ],
            null,
            201
        );
    }

    private function dayToIso(string|int|null $day): ?int
    {
        if ($day === null) {
            return null;
        }

        if (is_int($day) || (is_string($day) && ctype_digit($day))) {
            $n = (int) $day;
            return ($n >= 1 && $n <= 7) ? $n : null;
        }

        if (!is_string($day)) {
            return null;
        }

        return match (strtolower($day)) {
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
            default => null,
        };
    }
}
